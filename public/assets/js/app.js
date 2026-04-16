document.addEventListener('DOMContentLoaded', function () {
    const barcodeInput = document.getElementById('barcodeInput');
    if (barcodeInput) {
        barcodeInput.focus();
    }

    const paymentMethod = document.getElementById('paymentMethod');
    const amountPaidInput = document.getElementById('amountPaid');
    const posTotalInput = document.getElementById('posTotal');
    const cashReceivedGroup = document.getElementById('cashReceivedGroup');
    const changeSummary = document.getElementById('changeSummary');
    const changeAmount = document.getElementById('changeAmount');
    const changeWarning = document.getElementById('changeWarning');
    const checkoutModal = document.getElementById('checkoutModal');

    if (!paymentMethod || !amountPaidInput || !posTotalInput || !cashReceivedGroup || !changeSummary || !changeAmount || !changeWarning) {
        return;
    }

    const total = parseFloat(posTotalInput.value) || 0;

    const parseAmount = function () {
        const rawValue = amountPaidInput.value;
        if (!rawValue) {
            return 0;
        }

        const normalized = String(rawValue)
            .replace(/\s/g, '')
            .replace(/\./g, '')
            .replace(',', '.');

        return parseFloat(normalized) || 0;
    };

    const formatMoney = function (value) {
        return value.toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };

    const updateChange = function () {
        const isCash = paymentMethod.value === 'efectivo';

        cashReceivedGroup.classList.toggle('d-none', !isCash);
        changeSummary.classList.toggle('d-none', !isCash);
        amountPaidInput.required = isCash;

        if (!isCash) {
            amountPaidInput.value = '';
            changeAmount.textContent = '$ 0,00';
            changeWarning.textContent = '';
            return;
        }

        const paid = parseAmount();
        const difference = paid - total;
        const change = Math.max(0, difference);

        changeAmount.textContent = '$ ' + formatMoney(change);

        if (difference < 0) {
            changeWarning.textContent = 'Faltan $ ' + formatMoney(Math.abs(difference));
        } else {
            changeWarning.textContent = '';
        }
    };

    paymentMethod.addEventListener('change', updateChange);
    amountPaidInput.addEventListener('input', updateChange);
    amountPaidInput.addEventListener('change', updateChange);
    amountPaidInput.addEventListener('keyup', updateChange);

    if (checkoutModal) {
        checkoutModal.addEventListener('shown.bs.modal', function () {
            if (paymentMethod.value === 'efectivo') {
                amountPaidInput.focus();
            }
            updateChange();
        });
    }

    updateChange();
});
