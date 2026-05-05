import { ethers } from 'ethers';

/**
 * Страница контракта: вызов read/write через ABI (ethers 5).
 * Конфиг: JSON в #contract-show-config (см. show.blade.php).
 */
function initContractShow() {
    const cfgEl = document.getElementById('contract-show-config');
    if (!cfgEl || !cfgEl.textContent) {
        return;
    }

    let cfg;
    try {
        cfg = JSON.parse(cfgEl.textContent);
    } catch (e) {
        console.error('contract-show: invalid JSON config', e);
        return;
    }

    const contractView = cfg.contractView;
    const callUrl = cfg.callUrl;
    const sendUrl = cfg.sendUrl;
    const gnd = cfg.gnd || {};
    const GND_CONTRACT_PREFIX = gnd.contractAddressPrefix ?? 'GNDct';
    const GND_CONTRACT_LENGTH = gnd.contractAddressLength ?? 37;
    const GND_CONTRACT_HEX_LEN = gnd.contractAddressHexLength ?? 32;

    const csrf =
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function placeholderByType(type) {
        if (!type) return '';
        if (type === 'address') return '0x... или GN_...';
        if (type.startsWith('uint') || type.startsWith('int')) return 'Число (для uint256 — в wei)';
        if (type === 'bool') return 'true или false';
        if (type.startsWith('bytes')) return '0x... (hex)';
        return type;
    }

    function renderInputs(containerId, inputs) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';
        if (!inputs || !inputs.length) return;
        inputs.forEach(function (inp, i) {
            const type = inp.type || 'string';
            const name = inp.name || 'arg' + i;
            const label = document.createElement('label');
            label.className = 'form-label small text-secondary mb-0';
            label.textContent = name + ' (' + type + ')';
            const input = document.createElement('input');
            const isInt = /^(u?int|uint256|int256|uint8|int8)(\d*)$/.test(type);
            input.type = isInt ? 'text' : 'text';
            input.className =
                'form-control form-control-sm bg-dark text-light border-secondary font-monospace mb-1';
            input.placeholder = placeholderByType(type);
            input.dataset.type = type;
            input.dataset.name = name;
            if (type === 'bool') {
                input.placeholder = 'true | false';
            }
            container.appendChild(label);
            container.appendChild(input);
        });
    }

    function getEncodedDataForBlock(methodSig, inputsContainerId) {
        const abi = contractView.abi;
        if (!abi || !Array.isArray(abi)) return null;
        try {
            const iface = new ethers.utils.Interface(abi);
            const methodName = (methodSig || '').split('(')[0];
            const fragment = iface.getFunction(methodName);
            if (!fragment) return null;
            const container = document.getElementById(inputsContainerId);
            const inputEls = container ? container.querySelectorAll('input') : [];
            const args = [];
            fragment.inputs.forEach(function (inp, i) {
                const val = inputEls[i] ? inputEls[i].value : '';
                args.push(parseInputValue(inp.type, val));
            });
            return iface.encodeFunctionData(fragment, args);
        } catch (e) {
            console.error(e);
            return null;
        }
    }

    const ZERO_ADDRESS = '0x0000000000000000000000000000000000000000';
    const ZERO_BYTES32 = '0x' + '0'.repeat(64);

    function parseInputValue(type, val) {
        if (val === undefined || val === null) val = '';
        val = String(val).trim();
        if (type === 'address') {
            if (!val) return ZERO_ADDRESS;
            if (val.startsWith('0x') && /^0x[0-9a-fA-F]{40}$/.test(val)) return val;
            if (val.startsWith(GND_CONTRACT_PREFIX) && val.length === GND_CONTRACT_LENGTH) {
                const hex = val.slice(GND_CONTRACT_PREFIX.length);
                if (hex.length === GND_CONTRACT_HEX_LEN && /^[0-9a-fA-F]+$/.test(hex)) {
                    return '0x' + '0'.repeat(8) + hex.toLowerCase();
                }
            }
            if (val.startsWith('GN_')) return val;
            if (!val.startsWith('0x')) val = '0x' + val;
            return val;
        }
        if (type === 'bool') {
            if (!val) return false;
            return val === 'true' || val === '1';
        }
        if (/^(u?int|uint256|int256|uint8|int8)(\d*)$/.test(type)) return val === '' ? '0' : val;
        if (/^bytes\d*$/.test(type) || type === 'bytes32') {
            return !val || val.length < 2 ? ZERO_BYTES32 : val.startsWith('0x') ? val : '0x' + val;
        }
        return val || '';
    }

    document.querySelectorAll('.read-method-inputs').forEach(function (el) {
        const id = el.id;
        const inputs = el.dataset.inputs ? JSON.parse(el.dataset.inputs) : [];
        renderInputs(id, inputs);
    });
    document.querySelectorAll('.write-method-inputs').forEach(function (el) {
        const id = el.id;
        const inputs = el.dataset.inputs ? JSON.parse(el.dataset.inputs) : [];
        renderInputs(id, inputs);
    });

    function callReadMethod(index) {
        const block = document.querySelectorAll('.read-method-block')[index];
        if (!block) return;
        const method = block.dataset.method;
        const inputsId = 'read-inputs-' + index;
        const resultEl = document.getElementById('read-result-' + index);
        if (!resultEl) return;
        const data = getEncodedDataForBlock(method, inputsId);
        if (!data) {
            resultEl.innerHTML = '<span class="text-danger">Ошибка кодирования</span>';
            return;
        }
        resultEl.innerHTML = '<span class="text-secondary">…</span>';
        fetch(callUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                Accept: 'application/json',
            },
            body: JSON.stringify({ data: data }),
        })
            .then(function (r) {
                return r.json();
            })
            .then(function (res) {
                if (res.success && res.data && res.data.return_data) {
                    resultEl.innerHTML =
                        '<span class="text-success">' +
                        (res.data.return_data_decoded || res.data.decoded || res.data.return_data) +
                        '</span>';
                } else {
                    resultEl.innerHTML =
                        '<span class="text-danger">' + (res.error || 'Ошибка') + '</span>';
                }
            })
            .catch(function (e) {
                resultEl.innerHTML = '<span class="text-danger">' + e.message + '</span>';
            });
    }

    const readRoot = document.getElementById('read-contract-methods');
    readRoot &&
        readRoot.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-call-read');
            if (btn && btn.dataset.index !== undefined) {
                callReadMethod(parseInt(btn.dataset.index, 10));
            }
        });

    const btnCallAll = document.getElementById('btn-call-read-all');
    btnCallAll &&
        btnCallAll.addEventListener('click', function () {
            const blocks = document.querySelectorAll('.read-method-block');
            blocks.forEach(function (_, i) {
                callReadMethod(i);
            });
        });

    const writeRoot = document.getElementById('write-contract-methods');
    writeRoot &&
        writeRoot.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-send-write');
            if (!btn || btn.dataset.index === undefined) return;
            const from =
                document.getElementById('write-from-wallet') &&
                document.getElementById('write-from-wallet').value;
            if (!from) {
                const resEl = document.getElementById('write-result-' + btn.dataset.index);
                if (resEl) resEl.innerHTML = '<span class="text-warning">Выберите кошелёк</span>';
                return;
            }
            const index = parseInt(btn.dataset.index, 10);
            const block = document.querySelectorAll('.write-method-block')[index];
            if (!block) return;
            const method = block.dataset.method;
            const inputsId = 'write-inputs-' + index;
            const resultEl = document.getElementById('write-result-' + index);
            if (!resultEl) return;
            const data = getEncodedDataForBlock(method, inputsId);
            if (!data) {
                resultEl.innerHTML = '<span class="text-danger">Ошибка кодирования</span>';
                return;
            }
            resultEl.innerHTML = '<span class="text-secondary">…</span>';
            btn.disabled = true;
            fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ from: from, data: data, value: '0', gas_limit: 200000 }),
            })
                .then(function (r) {
                    return r.json();
                })
                .then(function (res) {
                    if (res.success && res.data) {
                        resultEl.innerHTML =
                            '<span class="text-success">Hash: ' + (res.data.hash || '') + '</span>';
                    } else {
                        resultEl.innerHTML =
                            '<span class="text-danger">' + (res.error || 'Ошибка') + '</span>';
                    }
                })
                .catch(function (er) {
                    resultEl.innerHTML = '<span class="text-danger">' + er.message + '</span>';
                })
                .finally(function () {
                    btn.disabled = false;
                });
        });
}

document.addEventListener('DOMContentLoaded', initContractShow);
