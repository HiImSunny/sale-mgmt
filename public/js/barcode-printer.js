window.BarcodePrinter = {
    defaultConfig: {
        quantity: 1,
        size: 'medium',
        showModal: true
    },

    sizeConfigs: {
        small: { width: 1, height: 30, fontSize: '10px' },
        medium: { width: 2, height: 50, fontSize: '12px' },
        large: { width: 3, height: 70, fontSize: '14px' }
    },

    print: function(data, options = {}) {
        const config = { ...this.defaultConfig, ...options };

        if (config.showModal) {
            this.showQuantityModal(data, config);
        } else {
            this.executePrint(data, config);
        }
    },

    showQuantityModal: function(data, config) {
        if (!document.getElementById('barcodeQuantityModal')) {
            this.createModal();
        }

        document.getElementById('modal-product-name').textContent = data.name;
        document.getElementById('modal-product-sku').textContent = data.sku;
        document.getElementById('quantity-input').value = config.quantity;
        document.getElementById('size-select').value = config.size;

        this.currentData = data;
        this.currentConfig = config;

        const modal = new bootstrap.Modal(document.getElementById('barcodeQuantityModal'));
        modal.show();
    },

    createModal: function() {
        const modalHTML = `
            <div class="modal fade" id="barcodeQuantityModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-print me-2"></i>In mã vạch
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <strong>Sản phẩm:</strong> <span id="modal-product-name"></span><br>
                                <strong>SKU:</strong> <code id="modal-product-sku"></code>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="quantity-input" class="form-label">Số lượng:</label>
                                    <input type="number" class="form-control" id="quantity-input"
                                           value="1" min="1" max="100">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="size-select" class="form-label">Kích thước:</label>
                                    <select class="form-select" id="size-select">
                                        <option value="small">Nhỏ</option>
                                        <option value="medium" selected>Vừa</option>
                                        <option value="large">Lớn</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Hủy
                            </button>
                            <button type="button" class="btn btn-success" onclick="BarcodePrinter.confirmPrint()">
                                <i class="fas fa-print me-2"></i>In ngay
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
    },

    confirmPrint: function() {
        const quantity = parseInt(document.getElementById('quantity-input').value) || 1;
        const size = document.getElementById('size-select').value;

        const modal = bootstrap.Modal.getInstance(document.getElementById('barcodeQuantityModal'));
        modal.hide();

        this.executePrint(this.currentData, { quantity, size, showModal: false });
    },

    executePrint: function(data, config) {
        const printWindow = window.open('', '_blank');
        const sizeConfig = this.sizeConfigs[config.size];

        const htmlContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>In mã vạch - ${data.name}</title>
                <meta charset="utf-8">
                <style>
                    @page {
                        size: A4;
                        margin: 5mm;
                    }

                    body {
                        font-family: Arial, sans-serif;
                        margin: 0;
                        padding: 10mm;
                        background: white;
                    }

                    .barcode-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 8mm;
                        justify-items: center;
                    }

                    .barcode-item {
                        border: 1px dashed #ccc;
                        padding: 3mm;
                        text-align: center;
                        background: #f8f9fa;
                        border-radius: 8px;
                        page-break-inside: avoid;
                        width: 100%;
                        max-width: 200px;
                    }

                    .product-name {
                        font-weight: bold;
                        font-size: ${sizeConfig.fontSize};
                        word-wrap: break-word;
                    }

                    .sku-text {
                        font-family: 'Courier New', monospace;
                        font-weight: bold;
                        font-size: ${sizeConfig.fontSize};
                    }

                    @media print {
                        body { -webkit-print-color-adjust: exact; }
                        .barcode-item { break-inside: avoid; }
                    }
                </style>
                <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
            </head>
            <body>
                <div class="barcode-grid">
                    ${Array.from({length: config.quantity}, (_, i) => `
                        <div class="barcode-item">
                            <div class="product-name">${data.name}</div>
                            <canvas id="barcode-${i}" class="barcode-canvas"></canvas>
                            <div class="sku-text">${data.sku}</div>
                        </div>
                    `).join('')}
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Generate barcodes
                        for (let i = 0; i < ${config.quantity}; i++) {
                            try {
                                JsBarcode("#barcode-" + i, "${data.sku}", {
                                    format: "CODE128",
                                    width: ${sizeConfig.width},
                                    height: ${sizeConfig.height},
                                    displayValue: false,
                                    margin: 5,
                                    background: "white",
                                    lineColor: "black"
                                });
                            } catch (e) {
                                console.error('Barcode generation failed:', e);
                            }
                        }

                        // Auto print
                        setTimeout(function() {
                            window.print();
                            setTimeout(() => window.close(), 1000);
                        }, 1000);
                    });
                </script>
            </body>
            </html>
        `;

        printWindow.document.write(htmlContent);
        printWindow.document.close();
    },
};
