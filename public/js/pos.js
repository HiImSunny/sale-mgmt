let cart = [];
let currentOrder = null;
let vnpayWindow = null;

let barcodeListenerActive = false;

let categories = [];
let searchTimeout = null;

let vnpayPopupWindow = null;
let paymentCheckInterval = null;
const POPUP_CHECK_INTERVAL = 1000;

document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);

    initializeEventListeners();
    loadCategories();
    initializeUnifiedSearch();
    initializeBarcodeScanner();

    setTimeout(() => {
        toggleBarcodeScanner();
    }, 1000);
});

function initializeEventListeners() {
    const confirmPaymentBtn = document.getElementById('confirm-payment');
    if (confirmPaymentBtn) {
        confirmPaymentBtn.addEventListener('click', confirmPayment);
    }

    const completeOrderBtn = document.getElementById('complete-order');
    if (completeOrderBtn) {
        completeOrderBtn.addEventListener('click', completeOrder);
        completeOrderBtn.style.display = 'none';
    }

    const searchInput = document.getElementById('unified-search');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearchInput);
        searchInput.addEventListener('keypress', handleSearchKeypress);
    }

    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', updatePaymentMethodUI);
    });
}

function initializeBarcodeScanner() {
    let keypressEvents = [];
    let timeout;

    document.addEventListener('keydown', function (e) {
        if (isInputElementFocused()) return;

        keypressEvents.push({
            key: e.key, time: Date.now()
        });

        clearTimeout(timeout);
        timeout = setTimeout(() => {
            processBarcodeInput(keypressEvents);
            keypressEvents = [];
        }, 100);

        if (barcodeListenerActive && keypressEvents.length > 1) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
}

function isInputElementFocused() {
    const focusedElement = document.activeElement;
    return focusedElement && (focusedElement.tagName === 'INPUT' || focusedElement.tagName === 'TEXTAREA' || focusedElement.tagName === 'SELECT' || focusedElement.contentEditable === 'true');
}

function processBarcodeInput(events) {
    if (events.length < 3) return;

    let totalTime = events[events.length - 1].time - events[0].time;
    let avgTimePerChar = totalTime / events.length;

    if (avgTimePerChar > 100) return;

    let barcode = events
        .filter(e => e.key !== 'Enter' && e.key.length === 1)
        .map(e => e.key)
        .join('');

    if (barcode.length >= 3) {
        handleBarcodeScanned(barcode);
    }
}

function toggleBarcodeScanner() {
    const statusDiv = document.getElementById('barcode-scanner-status');
    const scannerIcon = document.getElementById('scanner-icon');
    const scannerText = document.getElementById('scanner-text');

    if (barcodeListenerActive) {
        barcodeListenerActive = false;
        if (statusDiv) statusDiv.style.display = 'none';
        if (scannerIcon) scannerIcon.className = 'fas fa-barcode';
        if (scannerText) scannerText.textContent = 'Máy quét';

        showAlert('Đã tắt máy quét mã vạch', 'info');
    } else {
        barcodeListenerActive = true;
        if (statusDiv) statusDiv.style.display = 'block';
        if (scannerIcon) scannerIcon.className = 'fas fa-barcode text-success';
        if (scannerText) scannerText.textContent = 'Đang quét...';

        showAlert('Máy quét mã vạch đã sẵn sàng!', 'success');

        const searchInput = document.getElementById('unified-search');
        if (searchInput) searchInput.blur();
    }
}

function handleBarcodeScanned(barcode) {
    if (!barcodeListenerActive) return;

    console.log('Barcode scanned:', barcode);

    showAlert(`Máy quét: ${barcode}`, 'success');

    searchProductByCodeAndAddToCart(barcode);

    const scannerIcon = document.getElementById('scanner-icon');
    if (scannerIcon) {
        scannerIcon.className = 'fas fa-check text-success';
        setTimeout(() => {
            if (barcodeListenerActive) {
                scannerIcon.className = 'fas fa-barcode text-success';
            }
        }, 1000);
    }
}

function searchProductByCodeAndAddToCart(code) {
    showAlert(`🔍 Đang tìm sản phẩm: ${code}`, 'info');

    fetch(`/api/products/by-code?code=${encodeURIComponent(code)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const product = data.data;

                const productForCart = {
                    id: product.product_id,
                    variant_id: product.id,
                    name: product.name,
                    variant_name: product.variant_name,
                    sku: product.sku,
                    price: parseFloat(product.price),
                    sale_price: product.sale_price ? parseFloat(product.sale_price) : null,
                    final_price: parseFloat(product.price),
                    stock_quantity: parseInt(product.stock_quantity),
                    thumbnail: product.thumbnail
                };

                addToCart(productForCart);
                showAlert(`✅ Đã thêm "${product.variant_name || product.name}" vào giỏ hàng`, 'success');

            } else {
                showAlert(`❌ Không tìm thấy sản phẩm với mã: ${code}`, 'warning');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            showAlert(`❌ Lỗi khi tìm kiếm sản phẩm: ${code}`, 'danger');
        });
}

function initializeUnifiedSearch() {
    const searchInput = document.getElementById('unified-search');
    const searchResults = document.getElementById('search-results');

    if (!searchInput || !searchResults) return;

    searchInput.addEventListener('focus', showSearchResults);

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            hideSearchResults();
        }
    });
}

function handleSearchInput(e) {
    const query = e.target.value.trim();

    clearTimeout(searchTimeout);

    if (query.length >= 2) {
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    } else {
        hideSearchResults();
    }
}

function handleSearchKeypress(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const query = e.target.value.trim();
        if (query.length >= 2) {
            performSearch(query);
        }
    }
}

function performSearch(query) {
    const resultsContainer = document.getElementById('search-results');

    if (!query || query.length < 2) {
        hideSearchResults();
        return;
    }

    resultsContainer.innerHTML = `
        <div class="dropdown-item-text text-center py-3">
            <div class="spinner-border spinner-border-sm me-2"></div>
            Đang tìm kiếm "${query}"...
        </div>
    `;
    showSearchResults();

    fetch(`/api/products/search?q=${encodeURIComponent(query)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Search results:', data);
            // Sửa từ data.products thành data.data
            if (data.success && data.data) {
                displaySearchResults(data.data);
            } else {
                displaySearchResults([]);
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            resultsContainer.innerHTML = `
                <div class="dropdown-item-text text-center text-danger py-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Lỗi khi tìm kiếm: ${error.message}
                </div>
            `;
            showSearchResults();
        });
}

function handleImageError(imgElement) {
    const noImageUrl = '/images/no-image.png';
    imgElement.onerror = null; // Prevent infinite loop
    imgElement.src = noImageUrl;
}

function displaySearchResults(results) {
    const resultsContainer = document.getElementById('search-results');
    const noImageUrl = '/images/no-image.png';

    if (results.length === 0) {
        resultsContainer.innerHTML = `
            <div class="dropdown-item-text text-muted text-center py-4">
                <i class="fas fa-search-minus fa-2x mb-2"></i>
                <div>Không tìm thấy sản phẩm nào</div>
                <small class="text-muted">Thử từ khóa khác hoặc quét mã vạch</small>
            </div>
        `;
    } else {
        let html = '';
        results.forEach((product, index) => {
            const stockStatus = product.stock_quantity > 10 ? 'success' : product.stock_quantity > 0 ? 'warning' : 'danger';
            const stockText = product.stock_quantity > 10 ? 'Còn hàng' : product.stock_quantity > 0 ? `Còn ${product.stock_quantity}` : 'Hết hàng';

            let priceDisplay;
            try {
                if (product.sale_price && parseFloat(product.sale_price) > 0) {
                    priceDisplay = `
                        <small class="text-decoration-line-through text-muted">${formatCurrency(product.price)}</small><br>
                        <strong class="text-danger">${formatCurrency(product.sale_price)}</strong>
                    `;
                } else {
                    priceDisplay = `<strong class="text-primary">${formatCurrency(product.price)}</strong>`;
                }
            } catch (e) {
                priceDisplay = '<strong class="text-primary">Liên hệ</strong>';
            }

            const displayName = product.variant_name || product.name || 'Sản phẩm không tên';
            const safeName = displayName.replace(/"/g, '&quot;').replace(/'/g, '&#39;');

            // Smart image handling - check if thumbnail exists first
            let imageHTML;
            if (product.thumbnail && product.thumbnail.trim()) {
                // Try to load actual thumbnail first, fallback to no-image on error
                imageHTML = `<img src="${product.thumbnail}"
                                 class="rounded"
                                 width="50"
                                 height="50"
                                 style="object-fit: cover; border: 1px solid #dee2e6;"
                                 onerror="handleImageError(this)"
                                 alt="${safeName}">`;
            } else {
                // No thumbnail provided, use no-image directly
                imageHTML = `<img src="${noImageUrl}"
                                 class="rounded"
                                 width="50"
                                 height="50"
                                 style="object-fit: cover; border: 1px solid #dee2e6;"
                                 alt="${safeName}">`;
            }

            html += `
                <div class="dropdown-item search-result-item ${product.stock_quantity <= 0 ? 'disabled' : ''}"
                     data-index="${index}">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            ${imageHTML}
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-3" style="min-width: 0;">
                                    <h6 class="mb-1 fw-bold"
                                       style="max-width: 250px;"
                                       title="${safeName}">
                                        ${displayName}
                                    </h6>
                                    <div class="text-muted small">
                                        <i class="fas fa-barcode me-1"></i>
                                        <span class="fw-medium">${product.sku || 'N/A'}</span>
                                        ${product.categories ? `<span class="text-secondary"> • ${product.categories}</span>` : ''}
                                    </div>
                                    ${product.attributes ? `<div class="mt-1">
                                            <small class="badge bg-info bg-opacity-25 text-info">
                                                ${product.attributes}
                                            </small>
                                         </div>` : ''}
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <div class="price-display mb-1">${priceDisplay}</div>
                                    <span class="badge bg-${stockStatus} bg-opacity-75 small">
                                        ${stockText}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex-shrink-0 ms-2">
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </div>
                    </div>
                </div>
            `;
        });

        resultsContainer.innerHTML = html;

        results.forEach((product, index) => {
            const element = resultsContainer.querySelector(`[data-index="${index}"]`);
            if (element && !element.classList.contains('disabled')) {
                element.addEventListener('click', function () {
                    const productForCart = {
                        id: product.product_id,
                        variant_id: product.id,
                        name: product.name,
                        variant_name: product.variant_name,
                        sku: product.sku,
                        price: parseFloat(product.price),
                        sale_price: product.sale_price ? parseFloat(product.sale_price) : null,
                        final_price: parseFloat(product.final_price || product.price),
                        stock: parseInt(product.stock_quantity),
                        thumbnail: product.thumbnail
                    };

                    addToCart(productForCart);
                    hideSearchResults();

                    const searchInput = document.getElementById('unified-search');
                    if (searchInput) {
                        searchInput.value = '';
                    }
                });
            }
        });
    }

    showSearchResults();
}

function showSearchResults() {
    const resultsContainer = document.getElementById('search-results');
    if (resultsContainer) {
        resultsContainer.style.display = 'block';

        resultsContainer.scrollTop = 0;

        resultsContainer.style.zIndex = '1200';
    }
}

function hideSearchResults() {
    const resultsContainer = document.getElementById('search-results');
    if (resultsContainer) {
        resultsContainer.style.display = 'none';
    }
}

// ========================================
// CART MANAGEMENT
// ========================================
function addToCart(product) {

    const existingItem = cart.find(item => (item.variant_id && item.variant_id === product.variant_id) || (!item.variant_id && item.product_id === product.id));

    if (existingItem) {
        existingItem.quantity += 1;
        existingItem.line_total = existingItem.quantity * existingItem.unit_price;
    } else {
        const cartItem = {
            id: Date.now(),
            product_id: product.id,
            variant_id: product.variant_id || null,
            name: product.variant_name || product.name,
            sku: product.sku,
            unit_price: product.sale_price || product.price,
            quantity: 1,
            line_total: product.sale_price || product.price,
            thumbnail: product.thumbnail
        };

        cart.push(cartItem);
    }

    updateCartDisplay();
    showAlert(`Đã thêm "${product.variant_name || product.name}" vào giỏ hàng`, 'success');
}

function updateCartDisplay() {
    const cartItemsContainer = document.getElementById('cart-items');
    const cartSummary = document.getElementById('cart-summary');
    const confirmPaymentBtn = document.getElementById('confirm-payment');
    const noImageUrl = window.noImageUrl || '/images/no-image.png';

    if (!cartItemsContainer) return;

    if (cart.length === 0) {
        cartItemsContainer.innerHTML = `
            <div class="empty-cart text-center py-4">
                <i class="bi bi-cart text-muted fs-1"></i>
                <p class="text-muted">Chưa có sản phẩm nào</p>
            </div>
        `;
        if (cartSummary) cartSummary.style.display = 'none';
        if (confirmPaymentBtn) confirmPaymentBtn.disabled = true;
        updateCartCount(0);
        return;
    }

    let html = '';
    let subtotal = 0;

    cart.forEach(item => {
        subtotal += item.line_total;

        // Smart image handling cho cart
        let thumbnailHTML;
        if (item.thumbnail && item.thumbnail.trim()) {
            thumbnailHTML = `<img src="${item.thumbnail}"
                                 alt="${item.name}"
                                 class="rounded"
                                 style="width: 50px; height: 50px; object-fit: cover;"
                                 onerror="handleImageError(this)">`;
        } else {
            thumbnailHTML = `<img src="${noImageUrl}"
                                 alt="${item.name}"
                                 class="rounded"
                                 style="width: 50px; height: 50px; object-fit: cover;">`;
        }

        const safeName = item.name.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        const safeSku = item.sku.replace(/"/g, '&quot;').replace(/'/g, '&#39;');

        html += `
            <div class="cart-item" data-cart-id="${item.id}">
                <div class="d-flex align-items-center mb-3 p-3 border rounded">
                    <div class="flex-shrink-0 me-3">
                        ${thumbnailHTML}
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${safeName}</h6>
                        <small class="text-muted">SKU: ${safeSku}</small>
                        <div class="mt-2">
                            <span class="fw-bold text-primary">${formatCurrency(item.unit_price)}</span>
                            <span class="text-muted">x ${item.quantity}</span>
                        </div>
                    </div>
            <div class="flex-shrink-0 d-flex flex-column align-items-end">
                <div class="btn-group btn-group-sm mb-2" role="group">
                    <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                    <span class="btn btn-outline-secondary px-3">${item.quantity}</span>
                    <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${item.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
                </div>
                <div class="text-end">
                    <strong>${formatCurrency(item.line_total)}</strong>
                </div>
            </div>
        `;
    });

    cartItemsContainer.innerHTML = html;

    if (cartSummary) {
        cartSummary.style.display = 'block';
        cartSummary.innerHTML = `
            <div class="border-top pt-3">
                <div class="d-flex justify-content-between mb-2">
                    <span>Tạm tính (${cart.length} sản phẩm):</span>
                    <span class="fw-bold">${formatCurrency(subtotal)}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Giảm giá:</span>
                    <span>0đ</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold fs-5">Tổng cộng:</span>
                    <span class="fw-bold fs-5 text-primary">${formatCurrency(subtotal)}</span>
                </div>
            </div>
        `;
    }

    if (confirmPaymentBtn) {
        confirmPaymentBtn.disabled = false;
    }

    updateCartCount(cart.length);
}

function updateQuantity(cartId, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCart(cartId);
        return;
    }

    const item = cart.find(item => item.id === cartId);
    if (item) {
        item.quantity = newQuantity;
        item.line_total = item.quantity * item.unit_price;
        updateCartDisplay();
    }
}

function removeFromCart(cartId) {
    cart = cart.filter(item => item.id !== cartId);
    updateCartDisplay();
}

function clearCart() {
    if (cart.length === 0) return;

    if (confirm('Bạn có chắc chắn muốn xóa tất cả sản phẩm?')) {
        cart = [];
        updateCartDisplay();
        showAlert('Đã xóa tất cả sản phẩm', 'info');
    }
}

function updateCartCount(count) {
    const cartCountBadge = document.getElementById('cart-count');
    if (cartCountBadge) {
        cartCountBadge.textContent = count;
    }
}

// ========================================
// PAYMENT PROCESSING
// ========================================
function confirmPayment() {
    if (cart.length === 0) {
        showAlert('Giỏ hàng trống', 'warning');
        return;
    }

    const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
    if (!paymentMethod) {
        showAlert('Vui lòng chọn phương thức thanh toán', 'warning');
        return;
    }

    const orderData = {
        items: cart.map(item => ({
            product_id: item.product_id,
            variant_id: item.variant_id,
            quantity: item.quantity,
            unit_price: item.unit_price
        })), payment_method: paymentMethod
    };

    fetch('/pos/create-order', {
        method: 'POST', headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }, body: JSON.stringify(orderData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentOrder = data.data;

                if (data.data.payment_method === 'vnpay') {
                    processVNPayPayment(data.data);
                } else {
                    showCashPaymentInterface();
                }
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Có lỗi xảy ra khi tạo đơn hàng', 'danger');
        });
}

function showCashPaymentInterface() {
    showAlert('Đơn hàng đã được tạo thành công!', 'success');
    document.getElementById('confirm-payment').style.display = 'none';
    document.getElementById('complete-order').style.display = 'block';
}

function completeOrder() {
    if (!currentOrder) {
        showAlert('Không có đơn hàng để hoàn tất', 'warning');
        return;
    }

    const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;

    fetch(`/pos/confirm-payment/${currentOrder.order_id}`, {
        method: 'POST', headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }, body: JSON.stringify({
            payment_method: paymentMethod
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Hoàn tất đơn hàng!', 'success');

                currentOrder = null;
                cart = [];
                updateCartDisplay();
                document.getElementById('confirm-payment').style.display = 'block';
                document.getElementById('complete-order').style.display = 'none';

                if (data.data.invoice_url) {
                    setTimeout(() => {
                        if (confirm('Bạn có muốn in hóa đơn không?')) {
                            window.open(data.data.invoice_url, '_blank');
                        }
                    }, 1000);
                }
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Có lỗi xảy ra khi xác nhận thanh toán', 'danger');
        });
}

function processVNPayPayment(orderData) {
    showVNPayQRModal(orderData.vnpay_qr);
}

function showVNPayQRModal(qrData) {
    const modalHtml = `
    <div class="modal fade" id="vnpayQRModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-qrcode me-2"></i>
                        Thanh toán VNPay
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="cancelVNPayPayment()"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <h6>Thông tin thanh toán</h6>
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-2">
                                        <strong>Số tiền:</strong><br>
                                        <span class="h4 text-primary">${formatCurrency(qrData.amount)}</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Mã giao dịch:</strong><br>
                                        <code>${qrData.txn_ref}</code>
                                    </div>
                                    <div class="alert alert-info small">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Thanh toán qua link thanh toán VNPay
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="openVNPayWindow('${qrData.url}')">
                        <i class="fas fa-external-link-alt me-2"></i>
                        Mở cửa sổ thanh toán
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="switchToCash()">
                        <i class="fas fa-money-bill me-2"></i>
                        Chuyển sang tiền mặt
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="cancelVNPayPayment()">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>
`;


    const existingModal = document.getElementById('vnpayQRModal');
    if (existingModal) {
        existingModal.remove();
    }

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('vnpayQRModal'));
    modal.show();

    openVNPayWindow(qrData.url);
}

function openVNPayWindow(paymentUrl) {
    // Đóng popup cũ nếu có
    if (vnpayPopupWindow && !vnpayPopupWindow.closed) {
        vnpayPopupWindow.close();
    }

    // Clear interval cũ nếu có
    if (paymentCheckInterval) {
        clearInterval(paymentCheckInterval);
        paymentCheckInterval = null;
    }

    // Tính toán vị trí popup ở giữa màn hình
    const width = 800;
    const height = 700;
    const left = (window.screen.width / 2) - (width / 2);
    const top = (window.screen.height / 2) - (height / 2);

    const windowFeatures = `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes,status=yes,toolbar=no,menubar=no`;

    // Mở popup với VNPay URL
    vnpayPopupWindow = window.open(paymentUrl, 'vnpay_payment', windowFeatures);

    // Kiểm tra popup có bị block không
    if (!vnpayPopupWindow || vnpayPopupWindow.closed || typeof vnpayPopupWindow.closed == 'undefined') {
        showAlert('Popup bị chặn! Vui lòng cho phép popup cho trang này và thử lại.', 'warning');
        return;
    }

    // Focus popup window
    vnpayPopupWindow.focus();

    // Bắt đầu monitor popup status
    startPopupMonitoring();

    showAlert('Đã mở cửa sổ thanh toán VNPay. Vui lòng hoàn tất thanh toán.', 'info');
}

function startPopupMonitoring() {
    paymentCheckInterval = setInterval(() => {
        // Kiểm tra popup đã đóng chưa
        if (!vnpayPopupWindow || vnpayPopupWindow.closed) {
            clearInterval(paymentCheckInterval);
            paymentCheckInterval = null;

            // Popup đã đóng, check payment status
            handlePopupClosed();
        }
    }, POPUP_CHECK_INTERVAL);
}

function handlePopupClosed() {
    console.log('VNPay popup window closed, checking payment status...');

    if (!currentOrder || !currentOrder.order_id) {
        return;
    }

    // Hiển thị loading state
    showAlert('🔍 Đang kiểm tra kết quả thanh toán...', 'info');

    // Gọi API check payment status
    checkVNPayPaymentStatus(currentOrder.order_id);
}

function checkVNPayPaymentStatus(orderId) {
    fetch(`/pos/check-payment-status/${orderId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.payment_status === 'paid' || data.payment_status === 'completed') {
                    // Thanh toán thành công
                    handlePaymentSuccess(data);
                } else if (data.payment_status === 'pending') {
                    // Thanh toán đang pending, có thể check lại
                    handlePaymentPending();
                } else {
                    // Thanh toán thất bại hoặc cancelled
                    handlePaymentFailure();
                }
            } else {
                showAlert('Không thể kiểm tra trạng thái thanh toán. Vui lòng liên hệ hỗ trợ.', 'danger');
            }
        })
        .catch(error => {
            console.error('Error checking payment status:', error);
            showAlert('Lỗi khi kiểm tra trạng thái thanh toán. Vui lòng thử lại.', 'danger');
        });
}

function handlePaymentSuccess(paymentData) {
    // Đóng modal VNPay nếu đang mở
    const modal = bootstrap.Modal.getInstance(document.getElementById('vnpayQRModal'));
    if (modal) {
        modal.hide();
    }

    Swal.fire({
        icon: 'success',
        title: '🎉 Thanh toán thành công!',
        html: `
            <div class="text-center">
                <h5>Đơn hàng: <strong>${currentOrder.order_code || currentOrder.order_id}</strong></h5>
                <p class="text-muted">Số tiền: <strong class="text-success">${formatCurrency(paymentData.amount || 0)}đ</strong></p>
                <p class="text-success"><i class="fas fa-check-circle me-2"></i>Giao dịch đã được xác nhận</p>
            </div>
        `,
        confirmButtonText: 'Hoàn tất',
        confirmButtonColor: '#28a745',
        allowOutsideClick: false
    }).then(() => {
        // Reset POS state
        completeOrderSuccess();

        // Hỏi in hóa đơn nếu có
        if (paymentData.invoice_url) {
            setTimeout(() => {
                Swal.fire({
                    title: 'In hóa đơn?',
                    text: 'Bạn có muốn in hóa đơn không?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'In hóa đơn',
                    cancelButtonText: 'Bỏ qua',
                    confirmButtonColor: '#007bff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(paymentData.invoice_url, '_blank');
                    }
                });
            }, 500);
        }
    });
}

function handlePaymentPending() {
    Swal.fire({
        title: '⏳ Thanh toán đang xử lý',
        text: 'Giao dịch đang được xử lý. Vui lòng đợi trong giây lát...',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Kiểm tra lại',
        cancelButtonText: 'Đóng',
        timer: 10000,
        timerProgressBar: true
    }).then((result) => {
        if (result.isConfirmed) {
            checkVNPayPaymentStatus(currentOrder.order_id);
        }
    });
}

function handlePaymentFailure() {
    Swal.fire({
        icon: 'error',
        title: '❌ Thanh toán không thành công',
        html: `
            <div class="text-center">
                <p>Giao dịch chưa được hoàn tất hoặc đã bị hủy.</p>
                <p class="text-muted">Bạn có thể thử lại hoặc chọn phương thức thanh toán khác.</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Thử lại',
        cancelButtonText: 'Chuyển sang tiền mặt',
        confirmButtonColor: '#007bff',
        cancelButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            // Thử lại VNPay
            if (currentOrder && currentOrder.vnpay_qr) {
                processVNPayPayment(currentOrder);
            }
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Chuyển sang tiền mặt
            switchToCash();
        }
    });
}

function switchToCash() {
    if (confirm('Bạn có muốn chuyển đơn hàng này sang thanh toán bằng tiền mặt không?')) {
        if (currentOrder) {
            updatePaymentMethodToCash(currentOrder.order_id);
        }
        cancelVNPayPayment();
    }
}

function updatePaymentMethodToCash(orderId) {
    fetch(`/pos/update-payment-method/${orderId}`, {
        method: 'POST', headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }, body: JSON.stringify({
            payment_method: 'cash_at_counter'
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Đã chuyển sang thanh toán tiền mặt', 'success');
                showCashPaymentInterface();
            }
        });
}

function cancelVNPayPayment() {
    // Đóng popup window
    if (vnpayPopupWindow && !vnpayPopupWindow.closed) {
        vnpayPopupWindow.close();
    }

    // Clear monitoring interval
    if (paymentCheckInterval) {
        clearInterval(paymentCheckInterval);
        paymentCheckInterval = null;
    }

    // Đóng modal
    const modalElement = document.getElementById('vnpayQRModal');
    const modalInstance = bootstrap.Modal.getInstance(modalElement);

    if (modalInstance) {
        modalInstance.hide();
    }

    // Cleanup modal
    setTimeout(() => {
        if (modalElement) {
            modalElement.remove();
        }

        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());

        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }, 300);

    // Reset variables
    vnpayPopupWindow = null;
    currentOrder = null;
    clearCart();
}

window.addEventListener('message', function (event) {
    if (event.data.type === 'vnpay_payment_result') {
        if (vnpayWindow && !vnpayWindow.closed) {
            vnpayWindow.close();
        }

        const modal = bootstrap.Modal.getInstance(document.getElementById('vnpayQRModal'));
        if (modal) {
            modal.hide();
        }

        if (event.data.success) {
            showAlert('Thanh toán VNPay thành công!', 'success');
            showCompletedOrderInterface(event.data.order);
        } else {
            showAlert('Thanh toán VNPay thất bại: ' + event.data.message, 'error');
        }
    }
});

function showCompletedOrderInterface(order) {
    currentOrder = order;

    document.getElementById('confirm-payment').style.display = 'none';
    document.getElementById('complete-order').style.display = 'block';

    showAlert(`Đơn hàng #${order.code} đã thanh toán thành công!`, 'success');

    clearCart();
}

// ========================================
// UTILITY FUNCTIONS
// ========================================
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount);
}

function updatePaymentMethodUI() {
    // Update UI based on selected payment method
    const selectedMethod = document.querySelector('input[name="payment_method"]:checked')?.value;

    // You can add specific UI updates here based on payment method
    console.log('Payment method selected:', selectedMethod);
}

function loadCategories() {
    // Load categories for filtering (if needed)
    // Implementation depends on your backend API
}

function showAlert(message, type = 'info') {
    console.log(`${type.toUpperCase()}: ${message}`);

    // Configure toast based on type
    let toastConfig = {
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    };

    // Set icon and styling based on type
    switch (type) {
        case 'success':
            toastConfig.icon = 'success';
            toastConfig.title = message;
            toastConfig.background = '#d1ecf1';
            toastConfig.color = '#0c5460';
            break;

        case 'danger':
        case 'error':
            toastConfig.icon = 'error';
            toastConfig.title = message;
            toastConfig.background = '#f8d7da';
            toastConfig.color = '#721c24';
            toastConfig.timer = 4000; // Longer for errors
            break;

        case 'warning':
            toastConfig.icon = 'warning';
            toastConfig.title = message;
            toastConfig.background = '#fff3cd';
            toastConfig.color = '#856404';
            break;

        case 'info':
        default:
            toastConfig.icon = 'info';
            toastConfig.title = message;
            toastConfig.background = '#cce7ff';
            toastConfig.color = '#004085';
            break;
    }

    // Show the toast
    Swal.fire(toastConfig);
}
