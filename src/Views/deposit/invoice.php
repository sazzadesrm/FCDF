<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="<?= asset_url('assets/css/invoice.css') ?>">

<?php
// BDT display: "Tk. 10,00,000/-" (lakh/crore grouping; decimals only when needed)
if (!function_exists('format_bdt')) {
  function format_bdt($n) {
    $n = round((float) $n, 2);
    $neg = $n < 0; $n = abs($n);
    $int = (string) floor($n);
    $dec = round(($n - floor($n)) * 100);
    if (strlen($int) > 3) {
      $last3 = substr($int, -3);
      $rest  = substr($int, 0, -3);
      $rest  = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
      $int   = $rest . ',' . $last3;
    }
    $out = $int . ($dec > 0 ? '.' . str_pad((string) (int) $dec, 2, '0', STR_PAD_LEFT) : '');
    return 'Tk. ' . ($neg ? '-' : '') . $out . '/-';
  }
}
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 no-print gap-2">
  <h4 class="mb-0">Invoice <?= e($deposit['invoice_number']) ?></h4>
  <div class="d-flex flex-wrap gap-2">
    <?php if (is_admin() || (int) $deposit['created_by'] === (int) current_user()['id']): ?>
      <a href="<?= route_url('deposit/edit', ['id' => $deposit['id']]) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
    <?php endif; ?>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="fa4Print()">Print</button>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="fa4Export('pdf')">Export PDF</button>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="fa4Export('jpg')">Export JPG</button>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="fa4Export('png')">Export PNG</button>
    <a href="<?= route_url('invoices') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
  </div>
</div>

<?php
// Footer stamps: 05-October-2026 at 09.42 PM
$fa4Stamp = function ($value): string {
    if (empty($value)) return '—';
    return format_date($value, 'd-F-Y') . ' at ' . format_date($value, 'h.i A');
};
$fa4Submitted = $fa4Stamp($deposit['created_at']);
$fa4Modified  = $fa4Stamp(!empty($deposit['updated_at']) ? $deposit['updated_at'] : $deposit['created_at']);
$fa4Terms = \App\Models\TermsCondition::where(['is_active' => 1], 'sort_order');
?>

<!-- ===== Source blocks (flowed into A4 pages by JS) ===== -->
<div id="fa4Source" class="fa4-source">

  <div class="fa4-block">
    <div class="fa4-head">
      <div class="fa4-brand">
        <div>
          <div class="fa4-org">Walton Hi-Tech Industries PLC (WHIPLC)</div>
          <div class="fa4-subheading">Advance Against Travelling &amp; Tour (Oracle New- 6996)</div>
        </div>
      </div>
      <div class="fa4-docbox">
        <div class="fa4-doctype">Invoice</div>
        <div class="fa4-docno"><span class="fa4-docno-v"><?= e($deposit['invoice_number']) ?></span></div>
        <div class="fa4-docdate"><span class="fa4-docdate-v"><?= format_date($deposit['deposit_date']) ?></span></div>
      </div>
    </div>
    <div class="fa4-rule"></div>
    <h2 class="fa4-title">Foreign Currency Deposit Transfer Form</h2>
  </div>

  <div class="fa4-block">
    <div class="fa4-cards">
      <div class="fa4-card">
        <div class="fa4-card-h">Employee Details</div>
        <div class="fa4-card-b">
          <div class="fa4-row"><span class="fa4-label">Name</span><span class="fa4-value"><?= e($deposit['name']) ?></span></div>
          <div class="fa4-row"><span class="fa4-label">ID No.</span><span class="fa4-value"><?= e($deposit['id_no']) ?></span></div>
          <div class="fa4-row"><span class="fa4-label">Department</span><span class="fa4-value"><?= e($deposit['department_name'] ?: '—') ?></span></div>
          <div class="fa4-row"><span class="fa4-label">Product</span><span class="fa4-value"><?= e($deposit['product_name'] ?: '—') ?></span></div>
        </div>
      </div>
      <div class="fa4-card">
        <div class="fa4-card-h">Voucher Details</div>
        <div class="fa4-card-b">
          <div class="fa4-row"><span class="fa4-label">Operating Unit</span><span class="fa4-value"><?= e($deposit['operating_unit_name'] ?: '—') ?></span></div>
          <div class="fa4-row"><span class="fa4-label">Legal Entity</span><span class="fa4-value"><?= e($deposit['legal_entity_name'] ?: '—') ?></span></div>
          <div class="fa4-row"><span class="fa4-label">Visited Country</span><span class="fa4-value"><?= e($deposit['country_name'] ?: '—') ?></span></div>
          <div class="fa4-row"><span class="fa4-label">EBS Number</span><span class="fa4-value"><?= e($deposit['ebs_number'] ?: '—') ?></span></div>
        </div>
      </div>
    </div>
    <div class="fa4-purpose">
      <span class="fa4-label">Purpose of Tour</span>
      <span class="fa4-value"><?= e($deposit['purpose_name'] ?: '—') ?><?= $deposit['purpose_of_tour_text'] ? ' — ' . e($deposit['purpose_of_tour_text']) : '' ?></span>
    </div>
  </div>

  <div class="fa4-block">
    <table class="fa4-table">
      <thead><tr><th>Currency</th><th class="fa4-num">Amount</th><th class="fa4-num">Rate</th><th class="fa4-num">BDT Amount</th></tr></thead>
      <tbody>
        <?php foreach ($deposit['lines'] as $line): ?>
          <tr>
            <td><?= e($line['currency_code']) ?> — <?= e($line['currency_name']) ?></td>
            <td class="fa4-num"><?= format_money((float) $line['amount']) ?></td>
            <td class="fa4-num"><?= number_format((float) $line['rate'], 4) ?></td>
            <td class="fa4-num"><?= format_bdt($line['bdt_amount']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="fa4-block">
    <div class="fa4-total">
      <div class="fa4-total-l">
        <div class="fa4-total-k">Amount in Words</div>
        <div class="fa4-words"><?= e($deposit['amount_in_words']) ?></div>
      </div>
      <div class="fa4-total-r">
        <div class="fa4-total-k">Total Deposited (BDT)</div>
        <div class="fa4-total-amt"><?= format_bdt($deposit['total_bdt_amount']) ?></div>
      </div>
    </div>
  </div>

  <?php if (!empty($deposit['special_note'])): ?>
    <div class="fa4-block fa4-note"><strong>Special Note:</strong> <?= nl2br(e($deposit['special_note'])) ?></div>
  <?php endif; ?>

  <?php $termNo = 1; foreach ($fa4Terms as $t): ?>
    <div class="fa4-block fa4-term">
      <?php if ($termNo === 1): ?><div class="fa4-terms-heading">Terms &amp; Conditions:</div><?php endif; ?>
      <div class="fa4-term-row">
        <span class="fa4-term-no"><?= $termNo++ ?>.</span>
        <span class="fa4-term-text"><?= e($t['content']) ?></span>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- Signature panel: placed at the bottom of the LAST page -->
  <div class="fa4-block fa4-sign" id="fa4Sign">
    <div class="fa4-sign-row">
      <div class="fa4-sign-cell">
        <div class="fa4-sign-cap">Depositor</div>
        <div class="fa4-sign-line">
          <?php if (!empty($deposit['depositor_name'])): ?>
            <span class="fa4-sign-name"><?= e($deposit['depositor_name']) ?></span><br>
            <span class="fa4-muted"><?= e($deposit['depositor_designation'] ?: 'Depositor') ?></span>
          <?php elseif (!empty($deposit['depositor_manual_name'])): ?>
            <span class="fa4-sign-name"><?= e($deposit['depositor_manual_name']) ?></span><br>
            <span class="fa4-muted">
              <?= $deposit['depositor_manual_id'] ? 'ID: ' . e($deposit['depositor_manual_id']) : '' ?>
              <?= ($deposit['depositor_manual_id'] && $deposit['depositor_manual_phone']) ? ' · ' : '' ?>
              <?= $deposit['depositor_manual_phone'] ? 'Phone: ' . e($deposit['depositor_manual_phone']) : '' ?>
              <?= (!$deposit['depositor_manual_id'] && !$deposit['depositor_manual_phone']) ? 'Depositor' : '' ?>
            </span>
          <?php else: ?>
            &nbsp;<br><span class="fa4-muted">Depositor</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="fa4-sign-cell">
        <div class="fa4-sign-cap">Verified By</div>
        <div class="fa4-sign-line">
          <span class="fa4-sign-name"><?= e($deposit['verifier_name'] ?: 'Verified By') ?></span><br>
          <span class="fa4-muted"><?= e($deposit['verifier_designation'] ?: 'Verified By') ?></span>
        </div>
      </div>
      <div class="fa4-sign-cell">
        <div class="fa4-sign-cap">Received By</div>
        <div class="fa4-sign-line">
          <span class="fa4-sign-name"><?= e($deposit['receiver_name'] ?: 'Received By') ?></span><br>
          <span class="fa4-muted"><?= e($deposit['receiver_designation'] ?: 'Received By') ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Page template: single A4 sheet — body, footnote + page/submitted/modified line -->
<template id="fa4PageTpl">
  <section class="fa4-page">
    <div class="fa4-topbar"></div>
    <div class="fa4-body"></div>
    <div class="fa4-footer">
      <div class="fa4-finePrint">For accounting related problem, please contact with Md. Arifur Rahman Khan (+88-01678-049106).</div>
      <div class="fa4-stamps">
        <div class="fa4-stamp fa4-stamp-left"><b class="fa4-stamp-k">Submitted</b> <?= e($fa4Submitted) ?></div>
        <div class="fa4-stamp fa4-stamp-mid fa4-pageno">Page 01 of 01</div>
        <div class="fa4-stamp fa4-stamp-right"><b class="fa4-stamp-k">Modified</b> <?= e($fa4Modified) ?></div>
      </div>
    </div>
  </section>
</template>

<noscript><style>#fa4Source{display:block!important}</style></noscript>

<div class="fa4-stage" id="invoiceCapture"><div id="fa4Pages"></div></div>

<script>
(function () {
  // ---------- Single-page layout: everything must fit ONE A4 sheet ----------
  // All blocks go onto one page. If the content is taller than the sheet, the
  // layout is tightened step by step (compact spacing levels, then a gentle
  // scale-down as a last resort) until it fits. It never spills to a 2nd page.
  var cache = null;
  var LEVELS = ['', 'fa4-compact-1', 'fa4-compact-2', 'fa4-compact-3'];

  function paginate() {
    var src = document.getElementById('fa4Source');
    var host = document.getElementById('fa4Pages');
    var tpl = document.getElementById('fa4PageTpl');
    if (!src || !host || !tpl) return;

    // Blocks are MOVED into the page, so cache them before the first run.
    if (!cache) {
      cache = Array.prototype.slice.call(src.querySelectorAll(':scope > .fa4-block'));
    }
    var sign = document.getElementById('fa4Sign');
    var blocks = cache.filter(function (b) { return b !== sign; });
    cache.forEach(function (b) { src.appendChild(b); });   // reset
    host.innerHTML = '';

    var page = tpl.content.firstElementChild.cloneNode(true);
    page.classList.add('fa4-first');
    host.appendChild(page);
    var body = page.querySelector('.fa4-body');
    blocks.forEach(function (b) { body.appendChild(b); });
    body.appendChild(sign);                                 // signature sits at the bottom

    function overflows() { return body.scrollHeight > body.clientHeight + 1; }

    // 1) Tighten spacing/typography level by level
    for (var i = 0; i < LEVELS.length; i++) {
      LEVELS.forEach(function (c) { if (c) page.classList.remove(c); });
      if (LEVELS[i]) page.classList.add(LEVELS[i]);
      if (!overflows()) break;
    }

    // 2) Last resort: scale the body down (min 60%) so nothing is ever cut off
    body.style.cssText = '';
    if (overflows()) {
      var W = body.clientWidth, H = body.clientHeight, s = 1;
      body.style.flex = 'none';
      body.style.transformOrigin = 'top left';
      while (s > 0.6) {
        s = Math.round((s - 0.02) * 100) / 100;
        body.style.width = (W / s) + 'px';
        body.style.height = (H / s) + 'px';
        body.style.transform = 'scale(' + s + ')';
        if (!overflows()) break;
      }
      body.style.marginBottom = (-(H / s - H)) + 'px';
    }

    page.querySelector('.fa4-pageno').textContent = 'Page 01 of 01';
    host.setAttribute('data-pages', 1);
  }

  // Load the premium fonts BEFORE paginating/exporting so page breaks and PNG/PDF match the screen.
  var fontSpecs = ['400 12px "Inter"', '500 12px "Inter"', '600 12px "Inter"', '700 12px "Inter"',
                   '800 16px "Inter"'];
  function loadFonts() {
    if (!document.fonts || !document.fonts.load) return Promise.resolve();
    var all = Promise.all(fontSpecs.map(function (f) { return document.fonts.load(f).catch(function () {}); }))
      .then(function () { return document.fonts.ready; });
    var timeout = new Promise(function (r) { setTimeout(r, 4000); });   // never block if offline
    return Promise.race([all, timeout]);
  }
  loadFonts().then(paginate);
  window.addEventListener('load', paginate);
  window.fa4Paginate = paginate;

  // ---------- Helpers ----------
  function loadScript(src) {
    return new Promise(function (res, rej) {
      var s = document.createElement('script');
      s.src = src; s.onload = res; s.onerror = function () { rej(new Error('Could not load ' + src)); };
      document.head.appendChild(s);
    });
  }
  function ensureH2C() {
    if (window.html2canvas) return Promise.resolve();
    return loadScript('https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js');
  }
  function ensurePdf() {
    if ((window.jspdf && window.jspdf.jsPDF) || window.jsPDF) return Promise.resolve();
    return loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js');
  }
  function logExport(type) {
    if (typeof window.fcdfLogExport === 'function') {
      try { window.fcdfLogExport('<?= route_url('invoice/log-export') ?>', { id: '<?= (int) $deposit['id'] ?>', type: type }); } catch (e) {}
    }
  }
  function pageNodes() { return Array.prototype.slice.call(document.querySelectorAll('#fa4Pages .fa4-page')); }

  // Every page is rendered at the exact A4 box (794 x 1123 px) -> identical to print.
  function capture(node) {
    return window.html2canvas(node, {
      scale: 2, backgroundColor: '#ffffff', useCORS: true,
      width: 794, height: 1123, windowWidth: 1200, scrollX: 0, scrollY: -window.scrollY
    });
  }
  function downloadBlob(blob, name) {
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob); a.download = name;
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(function () { URL.revokeObjectURL(a.href); }, 4000);
  }
  function wait(ms) { return new Promise(function (r) { setTimeout(r, ms); }); }

  window.fa4Print = function () {
    logExport('print');
    window.print();           // @media print = the same A4 pages as the export
  };

  window.fa4Export = async function (type) {
    var base = '<?= e(preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $deposit['invoice_number'])) ?>';
    var btns = document.querySelectorAll('.no-print button');
    btns.forEach(function (b) { b.disabled = true; });
    try {
      logExport(type);
      await loadFonts();
      var libs = [ensureH2C()]; if (type === 'pdf') libs.push(ensurePdf());
      await Promise.all(libs);
      var nodes = pageNodes();
      document.body.classList.add('fa4-exporting');

      if (type === 'pdf') {
        var J = (window.jspdf && window.jspdf.jsPDF) || window.jsPDF;
        var pdf = new J({ unit: 'mm', format: 'a4', orientation: 'portrait', compress: true });
        for (var i = 0; i < nodes.length; i++) {
          var c = await capture(nodes[i]);
          if (i > 0) pdf.addPage('a4', 'portrait');
          pdf.addImage(c.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 0, 210, 297, undefined, 'FAST');
        }
        pdf.save(base + '.pdf');
      } else {
        var mime = type === 'jpg' ? 'image/jpeg' : 'image/png';
        for (var k = 0; k < nodes.length; k++) {
          var cv = await capture(nodes[k]);
          var blob = await new Promise(function (r) { cv.toBlob(r, mime, 0.95); });
          var suffix = nodes.length > 1 ? '_page-' + (k + 1 < 10 ? '0' : '') + (k + 1) : '';
          downloadBlob(blob, base + suffix + '.' + type);
          if (k < nodes.length - 1) await wait(400);
        }
      }
    } catch (err) {
      console.error(err);
      alert('Export failed. Please try again.');
    } finally {
      document.body.classList.remove('fa4-exporting');
      btns.forEach(function (b) { b.disabled = false; });
    }
  };
})();
</script>