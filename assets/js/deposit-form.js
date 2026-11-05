/**
 * FCDF Pro - Deposit Form dynamic behavior
 * Handles: Add/Remove currency rows, live BDT calculation per row,
 * live grand total, and a client-side preview of amount-in-words
 * (server always re-validates/recalculates on submit).
 */
(function () {
  var ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
    'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
  var tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

  function twoDigits(n) {
    if (n < 20) return ones[n];
    var t = Math.floor(n / 10), o = n % 10;
    return (tens[t] + ' ' + ones[o]).trim();
  }

  function convertInt(number) {
    if (number === 0) return 'Zero';
    var parts = [];
    var crore = Math.floor(number / 10000000); number %= 10000000;
    var lakh = Math.floor(number / 100000); number %= 100000;
    var thousand = Math.floor(number / 1000); number %= 1000;
    var hundred = Math.floor(number / 100);
    var remainder = number % 100;
    if (crore) parts.push(twoDigits(crore) + ' Crore');
    if (lakh) parts.push(twoDigits(lakh) + ' Lakh');
    if (thousand) parts.push(twoDigits(thousand) + ' Thousand');
    if (hundred) parts.push(ones[hundred] + ' Hundred');
    if (remainder) parts.push(twoDigits(remainder));
    return parts.join(' ');
  }

  function amountInWords(amount) {
    var rupees = Math.floor(amount);
    var words = 'Taka ' + convertInt(rupees) + ' Only';
    return words.replace(/\s+/g, ' ').trim();
  }

  function recalcRow(row) {
    var amount = Math.round(parseFloat(row.querySelector('.line-amount').value) || 0);
    var rate = parseFloat(row.querySelector('.line-rate').value) || 0;
    var bdt = Math.round(amount * rate);
    row.querySelector('.line-bdt').textContent = bdt.toLocaleString('en-US');
    row.dataset.bdt = bdt;
  }

  function recalcTotal() {
    var rows = document.querySelectorAll('#currencyRows .currency-row');
    var total = 0;
    rows.forEach(function (row) {
      recalcRow(row);
      total += parseFloat(row.dataset.bdt) || 0;
    });
    total = Math.round(total);
    var totalEl = document.getElementById('grandTotalBdt');
    if (totalEl) totalEl.textContent = total.toLocaleString('en-US');
    var wordsEl = document.getElementById('amountInWords');
    if (wordsEl) wordsEl.textContent = amountInWords(total);
  }

  function bindRow(row) {
    row.querySelectorAll('.line-amount, .line-rate').forEach(function (input) {
      input.addEventListener('input', recalcTotal);
    });
    var removeBtn = row.querySelector('.remove-row');
    if (removeBtn) {
      removeBtn.addEventListener('click', function () {
        var rows = document.querySelectorAll('#currencyRows .currency-row');
        if (rows.length > 1) {
          row.remove();
          recalcTotal();
        }
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('currencyRows');
    if (!container) return;

    document.querySelectorAll('#currencyRows .currency-row').forEach(bindRow);
    recalcTotal();

    var addBtn = document.getElementById('addCurrencyRow');
    var template = document.getElementById('currencyRowTemplate');
    if (addBtn && template) {
      addBtn.addEventListener('click', function () {
        var clone = template.content.cloneNode(true);
        container.appendChild(clone);
        var newRow = container.lastElementChild;
        bindRow(newRow);
        if (window.jQuery && jQuery.fn.select2) {
          jQuery(newRow).find('.searchable-select').select2({ width: '100%' });
        }
      });
    }
  });
})();
