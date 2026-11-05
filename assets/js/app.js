/**
 * FCDF Pro - global app JS
 * Initializes searchable dropdowns and shared export/print helpers.
 */
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && jQuery.fn.select2) {
      jQuery('.searchable-select').each(function () {
        var $el = jQuery(this);
        var isSmall = $el.hasClass('form-select-sm');
        $el.select2({
          width: '100%',
          containerCssClass: isSmall ? 'select2-sm' : '',
          dropdownCssClass: isSmall ? 'select2-sm' : '',
        });
      });
    }
  });

  // Records an export/print action to the audit trail via the given endpoint.
  window.fcdfLogExport = function (endpoint, params) {
    var body = new URLSearchParams(params);
    fetch(endpoint, { method: 'POST', body: body, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .catch(function () { /* non-blocking */ });
  };

  // ---------------------------------------------------------------------
  // Print-as-image. Renders the SAME html2canvas capture used by
  // Export PDF/JPG/PNG, then prints that captured image directly. This
  // guarantees Print output is pixel-identical to the exports — same
  // fonts, same spacing, same single-page A4 fit — because it's literally
  // the same rasterized image, rather than relying on the browser's
  // separate native print engine (which was producing visibly different
  // results, e.g. missing list numbering that html2canvas also can't
  // render — fixed at the source by no longer using native <ol> markers).
  // ---------------------------------------------------------------------
  window.fcdfPrintAsImage = function (selector, opts) {
    opts = opts || {};
    var el = document.querySelector(selector);
    if (!el) { window.print(); return; }

    var captureOptions = { scale: 2, useCORS: true, backgroundColor: '#ffffff' };
    if (opts.windowWidth) {
      captureOptions.windowWidth = opts.windowWidth;
      captureOptions.width = opts.windowWidth;
    }

    html2canvas(el, captureOptions).then(function (canvas) {
      var dataUrl = canvas.toDataURL('image/jpeg', 0.95);

      var container = document.createElement('div');
      container.id = 'fcdfPrintImageContainer';
      var img = document.createElement('img');
      img.src = dataUrl;
      container.appendChild(img);
      document.body.appendChild(container);
      document.body.classList.add('fcdf-printing-image');

      // For landscape documents (e.g. Reports), temporarily override the
      // page orientation for this print job only.
      var pageStyle = null;
      if (opts.landscape) {
        document.body.classList.add('fcdf-print-landscape');
        pageStyle = document.createElement('style');
        pageStyle.id = 'fcdfPrintPageStyle';
        pageStyle.textContent = '@page { size: A4 landscape; margin: 12mm; }';
        document.head.appendChild(pageStyle);
      }

      var cleanup = function () {
        document.body.classList.remove('fcdf-printing-image', 'fcdf-print-landscape');
        if (container.parentNode) container.parentNode.removeChild(container);
        if (pageStyle && pageStyle.parentNode) pageStyle.parentNode.removeChild(pageStyle);
        window.removeEventListener('afterprint', cleanup);
      };
      window.addEventListener('afterprint', cleanup);
      // Fallback for browsers/mobile webviews that don't reliably fire
      // 'afterprint' (notably some Android in-app browsers).
      setTimeout(cleanup, 6000);

      // Let the browser lay out the injected <img> before opening the
      // print dialog.
      setTimeout(function () { window.print(); }, 60);
    });
  };

  // Generic export-to-image/PDF helper using html2canvas + jsPDF.
  // targetSelector: the DOM node to capture. type: 'pdf' | 'jpg' | 'png'.
  // windowWidth forces html2canvas to render as if the browser were this
  // wide, so a formal single-page A4 document (e.g. the deposit voucher)
  // always exports looking like the desktop layout, even when triggered
  // from a phone where the on-screen view is responsively stacked.
  window.fcdfExport = function (targetSelector, type, filename, opts) {
    opts = opts || {};
    var el = document.querySelector(targetSelector);
    if (!el) return;
    var captureOptions = { scale: 2, useCORS: true, backgroundColor: '#ffffff' };
    if (opts.windowWidth) {
      captureOptions.windowWidth = opts.windowWidth;
      captureOptions.width = opts.windowWidth;
    }
    html2canvas(el, captureOptions).then(function (canvas) {
      if (type === 'pdf') {
        var orientation = opts.orientation === 'landscape' ? 'l' : 'p';
        var jsPDFCtor = window.jspdf ? window.jspdf.jsPDF : window.jsPDF;
        var pdf = new jsPDFCtor(orientation, 'mm', 'a4');
        var pageWidth = pdf.internal.pageSize.getWidth();
        var imgWidth = pageWidth;
        var imgHeight = (canvas.height * imgWidth) / canvas.width;
        var pageHeight = pdf.internal.pageSize.getHeight();
        if (imgHeight > pageHeight) {
          imgHeight = pageHeight;
          imgWidth = (canvas.width * imgHeight) / canvas.height;
        }
        var x = (pageWidth - imgWidth) / 2;
        pdf.addImage(canvas.toDataURL('image/jpeg', 0.95), 'JPEG', x, 0, imgWidth, imgHeight);
        pdf.save(filename + '.pdf');
      } else {
        var mime = type === 'jpg' ? 'image/jpeg' : 'image/png';
        var link = document.createElement('a');
        link.download = filename + '.' + type;
        link.href = canvas.toDataURL(mime, 0.95);
        link.click();
      }
    });
  };
})();
