let cart = [];
let currentMode = 'sale';
let selectedRefundOrder = null;
let refundItems = {};
let currentOrder = null;
let vnpayWindow = null;

// Barcode Scanner Variables
let barcodeListenerActive = false;

// Categories and Search
let categories = [];
let searchTimeout = null;

let vnpayPopupWindow = null;
let paymentCheckInterval = null;
const POPUP_CHECK_INTERVAL = 1000;

// ========================================
// INITIALIZATION
// ========================================
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const mode = urlParams.get('mode');
    if (mode === 'refund') {
        switchMode('refund');
    }

    initializeEventListeners();
    loadCategories();
    initializeUnifiedSearch();
    initializeBarcodeScanner();

    // Auto-enable barcode scanner for POS
    setTimeout(() => {
        toggleBarcodeScanner();
    }, 1000);
});

// ========================================
// EVENT LISTENERS INITIALIZATION
// ========================================
function initializeEventListeners() {
    // Confirm payment button
    const confirmPaymentBtn = document.getElementById('confirm-payment');
    if (confirmPaymentBtn) {
        confirmPaymentBtn.addEventListener('click', confirmPayment);
    }

    // Complete order button
    const completeOrderBtn = document.getElementById('complete-order');
    if (completeOrderBtn) {
        completeOrderBtn.addEventListener('click', completeOrder);
        completeOrderBtn.style.display = 'none';
    }

    // Search input events
    const searchInput = document.getElementById('unified-search');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearchInput);
        searchInput.addEventListener('keypress', handleSearchKeypress);
    }

    // Payment method change
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
    return focusedElement && (
        focusedElement.tagName === 'INPUT' ||
        focusedElement.tagName === 'TEXTAREA' ||
        focusedElement.tagName === 'SELECT' ||
        focusedElement.contentEditable === 'true'
    );
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

// ✅ FIXED: Updated API call to match new structure
function searchProductByCodeAndAddToCart(code) {
    showAlert(`🔍 Đang tìm sản phẩm: ${code}`, 'info');

    fetch(`/api/products/by-code?code=${encodeURIComponent(code)}`)
        .then(response => response.json())
        .then(data => {
            console.log('Product search response:', data);

            if (data.success && data.data) {
                if (data.type === 'variant') {
                    // Single variant found
                    const variant = data.data;
                    const productForCart = {
                        id: variant.product_id,
                        variant_id: variant.id,
                        name: variant.name,
                        variant_name: variant.name,
                        sku: variant.sku,
                        price: parseFloat(variant.price),
                        final_price: parseFloat(variant.price),
                        stock_quantity: parseInt(variant.stock_quantity),
                        thumbnail: variant.thumbnail
                    };

                    addToCart(productForCart);
                    showAlert(`✅ Đã thêm "${variant.name}" vào giỏ hàng`, 'success');

                } else if (data.type === 'product') {
                    // Simple product found
                    const product = data.data;
                    const productForCart = {
                        id: product.id,
                        variant_id: null,
                        name: product.name,
                        variant_name: product.name,
                        sku: product.sku,
                        price: parseFloat(product.price),
                        final_price: parseFloat(product.price),
                        stock_quantity: parseInt(product.stock_quantity),
                        thumbnail: product.thumbnail
                    };

                    addToCart(productForCart);
                    showAlert(`✅ Đã thêm "${product.name}" vào giỏ hàng`, 'success');

                } else if (data.type === 'product_with_variants') {
                    // Product with multiple variants - show selection modal
                    showVariantSelectionModal(data.data);

                } else {
                    showAlert(`❓ Không xác định được loại sản phẩm với mã: ${code}`, 'warning');
                }
            } else {
                showAlert(`❌ Không tìm thấy sản phẩm với mã: ${code}`, 'warning');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            showAlert(`❌ Lỗi khi tìm kiếm sản phẩm: ${code}`, 'danger');
        });
}

// ✅ NEW: Show variant selection modal
function showVariantSelectionModal(productData) {
    const modalHtml = `
        <div class="modal fade" id="variantSelectionModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Chọn biến thể sản phẩm</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6>${productData.name}</h6>
                        <p class="text-muted">SKU: ${productData.sku}</p>
                        <div class="row">
                            ${productData.variants.map(variant => `
                                <div class="col-12 mb-2">
                                    <button type="button" class="btn btn-outline-primary w-100 text-start"
                                            onclick="selectVariantFromModal(${variant.id}, '${productData.name}', '${variant.sku}', ${variant.price}, ${variant.stock_quantity})">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>SKU: ${variant.sku}</strong><br>
                                                <small class="text-muted">
                                                    ${variant.attributes.map(attr => `${attr.attribute_name}: ${attr.value}`).join(', ')}
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold">${formatCurrency(variant.price)}đ</div>
                                                <small class="badge ${variant.stock_quantity > 0 ? 'bg-success' : 'bg-danger'}">
                                                    ${variant.stock_quantity > 0 ? `Còn ${variant.stock_quantity}` : 'Hết hàng'}
                                                </small>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('variantSelectionModal');
    if (existingModal) {
        existingModal.remove();
    }

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('variantSelectionModal'));
    modal.show();
}

// ✅ NEW: Select variant from modal
function selectVariantFromModal(variantId, productName, sku, price, stockQuantity) {
    const productForCart = {
        id: null, // Will be set by backend
        variant_id: variantId,
        name: productName,
        variant_name: productName,
        sku: sku,
        price: parseFloat(price),
        final_price: parseFloat(price),
        stock_quantity: parseInt(stockQuantity),
        thumbnail: null
    };

    addToCart(productForCart);
    showAlert(`✅ Đã thêm "${productName}" vào giỏ hàng`, 'success');

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('variantSelectionModal'));
    if (modal) {
        modal.hide();
    }
}

// ========================================
// SEARCH FUNCTIONALITY
// ========================================
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
                priceDisplay = `<strong class="text-primary">${formatCurrency(product.price)}</strong>`;
            } catch (e) {
                priceDisplay = '<strong class="text-primary">Liên hệ</strong>';
            }

            const displayName = product.variant_name || product.name || 'Sản phẩm không tên';
            const safeName = displayName.replace(/"/g, '&quot;').replace(/'/g, '&#39;');


            let imageHTML;
            if (product.thumbnail && product.thumbnail.trim()) {
                imageHTML = `<img src="${product.thumbnail}"
                                 class="rounded"
                                 width="50"
                                 height="50"
                                 style="object-fit: cover; border: 1px solid #dee2e6;"
                                 onerror="handleImageError(this)"
                                 alt="${safeName}">`;
            } else {
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
                                        ${product.category ? `<span class="text-secondary"> • ${product.category}</span>` : ''}
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

        // Event handlers
        results.forEach((product, index) => {
            const element = resultsContainer.querySelector(`[data-index="${index}"]`);
            if (element && !element.classList.contains('disabled')) {
                element.addEventListener('click', function () {
                    const productForCart = {
                        id: product.product_id || product.id,
                        variant_id: product.type === 'variant' ? product.id : null,
                        name: product.name,
                        variant_name: product.variant_name || product.name,
                        sku: product.sku,
                        price: parseFloat(product.price),
                        final_price: parseFloat(product.price),
                        stock_quantity: parseInt(product.stock_quantity), // ✅ FIXED: Use stock_quantity
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
    if (currentMode === 'refund') {
        showAlert('Chế độ hoàn trả: Chọn sản phẩm từ đơn hàng', 'warning');
        return;
    }

    // ✅ FIXED: Check stock before adding
    if (product.stock_quantity <= 0) {
        showAlert('Sản phẩm đã hết hàng', 'warning');
        return;
    }

    const existingItem = cart.find(item =>
        (item.variant_id && item.variant_id === product.variant_id) ||
        (!item.variant_id && item.product_id === product.id)
    );

    if (existingItem) {
        // ✅ FIXED: Check stock when increasing quantity
        if (existingItem.quantity >= product.stock_quantity) {
            showAlert(`Không thể thêm. Chỉ còn ${product.stock_quantity} sản phẩm`, 'warning');
            return;
        }

        existingItem.quantity += 1;
        existingItem.line_total = existingItem.quantity * existingItem.unit_price;
    } else {
        const cartItem = {
            id: Date.now(),
            product_id: product.id,
            variant_id: product.variant_id || null,
            name: product.variant_name || product.name,
            sku: product.sku,
            unit_price: product.final_price || product.price,
            quantity: 1,
            line_total: product.final_price || product.price,
            max_stock: product.stock_quantity, // ✅ ADDED: Track max stock for validation
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
                <p class="text-muted">${currentMode === 'refund' ? 'Chưa chọn sản phẩm hoàn trả' : 'Chưa có sản phẩm nào'}</p>
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

        // Smart image handling
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
                        ${item.max_stock ? `<small class="text-info d-block">Tồn kho: ${item.max_stock}</small>` : ''}
                        <div class="mt-2">
                            <span class="fw-bold text-primary">${formatCurrency(item.unit_price)}</span>
                            <span class="text-muted">x ${item.quantity}</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0 d-flex flex-column align-items-end">
                        <div class="btn-group btn-group-sm mb-2" role="group">
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                            <span class="btn btn-outline-secondary px-3">${item.quantity}</span>
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="updateQuantity(${item.id}, ${item.quantity + 1})"
                                    ${item.max_stock && item.quantity >= item.max_stock ? 'disabled' : ''}>+</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="removeFromCart(${item.id})">
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

    // Update cart summary
    if (cartSummary) {
        cartSummary.style.display = 'block';
        const subtotalElement = document.getElementById('subtotal');
        const grandTotalElement = document.getElementById('grand-total');

        if (subtotalElement) subtotalElement.textContent = formatCurrency(subtotal);
        if (grandTotalElement) grandTotalElement.textContent = formatCurrency(subtotal);
    }

    if (confirmPaymentBtn) {
        confirmPaymentBtn.disabled = cart.length === 0;
    }

    updateCartCount(cart.length);
}

function updateQuantity(cartItemId, newQuantity) {
    const item = cart.find(cartItem => cartItem.id === cartItemId);
    if (!item) return;

    if (newQuantity <= 0) {
        removeFromCart(cartItemId);
        return;
    }

    // ✅ FIXED: Check stock limit when increasing quantity
    if (item.max_stock && newQuantity > item.max_stock) {
        showAlert(`Không thể tăng số lượng. Chỉ còn ${item.max_stock} sản phẩm`, 'warning');
        return;
    }

    item.quantity = newQuantity;
    item.line_total = item.quantity * item.unit_price;

    updateCartDisplay();
}

function removeFromCart(cartItemId) {
    cart = cart.filter(item => item.id !== cartItemId);
    updateCartDisplay();
    showAlert('Đã xóa sản phẩm khỏi giỏ hàng', 'info');
}

function clearCart() {
    if (cart.length === 0) {
        showAlert('Giỏ hàng đã trống', 'info');
        return;
    }

    if (confirm('Bạn có chắc muốn xóa tất cả sản phẩm trong giỏ hàng?')) {
        cart = [];
        updateCartDisplay();
        showAlert('Đã xóa tất cả sản phẩm', 'success');
    }
}

function updateCartCount(count) {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
        cartCountElement.style.display = count > 0 ? 'inline' : 'none';
    }
}

// ========================================
// ORDER PROCESSING
// ========================================
function completeOrder() {
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
            product_id: item.product_id || null,
            variant_id: item.variant_id || null,
            quantity: item.quantity,
            unit_price: item.unit_price
        })),
        payment_method: paymentMethod
    };

    // Show loading state
    const completeBtn = document.getElementById('complete-order');
    const originalText = completeBtn.textContent;
    completeBtn.disabled = true;
    completeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';

    fetch('/pos/create-order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(orderData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentOrder = data.data;

                if (paymentMethod === 'vnpay') {
                    // Handle VNPay QR
                    if (data.data.vnpay_qr) {
                        showVNPayModal(data.data.vnpay_qr, currentOrder);
                    } else {
                        showAlert('Lỗi tạo QR VNPay', 'danger');
                    }
                } else {
                    // Show payment confirmation for cash
                    showPaymentConfirmation(currentOrder);
                }
            } else {
                showAlert(data.message || 'Lỗi tạo đơn hàng', 'danger');
            }
        })
        .catch(error => {
            console.error('Order creation error:', error);
            showAlert('Lỗi kết nối. Vui lòng thử lại.', 'danger');
        })
        .finally(() => {
            completeBtn.disabled = false;
            completeBtn.textContent = originalText;
        });
}

function confirmPayment() {
    if (!currentOrder) {
        showAlert('Không có đơn hàng để xác nhận', 'warning');
        return;
    }

    const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;

    if (paymentMethod === 'cash_at_counter') {
        // Process cash payment
        fetch(`/pos/confirm-payment/${currentOrder.order_id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                payment_method: 'cash_at_counter'
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Thanh toán thành công!', 'success');

                    // Reset cart and UI
                    cart = [];
                    currentOrder = null;
                    updateCartDisplay();
                    hidePaymentModal();

                    // Open invoice
                    if (data.data.invoice_url) {
                        window.open(data.data.invoice_url, '_blank');
                    }
                } else {
                    showAlert(data.message || 'Lỗi xác nhận thanh toán', 'danger');
                }
            })
            .catch(error => {
                console.error('Payment confirmation error:', error);
                showAlert('Lỗi xác nhận thanh toán', 'danger');
            });
    }
}

function showPaymentConfirmation(orderData) {
    const modal = document.getElementById('paymentConfirmationModal');
    if (modal) {
        document.getElementById('order-code').textContent = orderData.order_code;
        document.getElementById('order-total').textContent = formatCurrency(orderData.total);

        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
}

function hidePaymentModal() {
    const modal = document.getElementById('paymentConfirmationModal');
    if (modal) {
        const bootstrapModal = bootstrap.Modal.getInstance(modal);
        if (bootstrapModal) {
            bootstrapModal.hide();
        }
    }
}

// ========================================
// VNPAY INTEGRATION
// ========================================
function showVNPayModal(qrData, orderData) {
    const modal = document.getElementById('vnpayModal');
    if (!modal) return;

    // Update modal content
    document.getElementById('vnpay-order-code').textContent = orderData.order_code;
    document.getElementById('vnpay-amount').textContent = formatCurrency(orderData.total);

    const qrContainer = document.getElementById('vnpay-qr-code');
    if (qrContainer && qrData.qr_code_url) {
        qrContainer.innerHTML = `
            <img src="${qrData.qr_code_url}"
                 alt="VNPay QR Code"
                 class="img-fluid"
                 style="max-width: 200px;">
        `;
    }

    // Show modal
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();

    // Start checking payment status
    startPaymentStatusCheck(orderData.order_id);
}

function startPaymentStatusCheck(orderId) {
    if (paymentCheckInterval) {
        clearInterval(paymentCheckInterval);
    }

    paymentCheckInterval = setInterval(() => {
        checkPaymentStatus(orderId);
    }, 3000); // Check every 3 seconds
}

function checkPaymentStatus(orderId) {
    fetch(`/pos/payment-status/${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.payment_status === 'paid') {
                    clearInterval(paymentCheckInterval);
                    handlePaymentSuccess(data);
                }
            }
        })
        .catch(error => {
            console.error('Payment status check error:', error);
        });
}

function handlePaymentSuccess(data) {
    showAlert('Thanh toán VNPay thành công!', 'success');

    // Hide VNPay modal
    const vnpayModal = document.getElementById('vnpayModal');
    if (vnpayModal) {
        const bootstrapModal = bootstrap.Modal.getInstance(vnpayModal);
        if (bootstrapModal) {
            bootstrapModal.hide();
        }
    }

    // Reset cart and state
    cart = [];
    currentOrder = null;
    updateCartDisplay();

    // Open invoice if available
    if (data.invoice_url) {
        window.open(data.invoice_url, '_blank');
    }
}

// ========================================
// MODE SWITCHING
// ========================================
function switchMode(mode) {
    currentMode = mode;

    const saleTab = document.getElementById('sale-tab');
    const refundTab = document.getElementById('refund-tab');
    const salePane = document.getElementById('sale-pane');
    const refundPane = document.getElementById('refund-pane');

    if (mode === 'sale') {
        saleTab?.classList.add('active');
        refundTab?.classList.remove('active');
        salePane?.classList.add('active', 'show');
        refundPane?.classList.remove('active', 'show');

        // Clear refund data
        selectedRefundOrder = null;
        refundItems = {};

        showAlert('Chuyển sang chế độ bán hàng', 'info');
    } else if (mode === 'refund') {
        refundTab?.classList.add('active');
        saleTab?.classList.remove('active');
        refundPane?.classList.add('active', 'show');
        salePane?.classList.remove('active', 'show');

        // Clear cart
        cart = [];
        updateCartDisplay();

        showAlert('Chuyển sang chế độ hoàn trả', 'info');
    }
}

// ========================================
// REFUND FUNCTIONALITY
// ========================================
function searchRefundOrder() {
    const query = document.getElementById('refund-order-search').value.trim();

    if (!query) {
        showAlert('Vui lòng nhập mã đơn hàng', 'warning');
        return;
    }

    fetch(`/pos/search-orders?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                displayRefundOrders(data.data);
            } else {
                displayRefundOrders([]);
            }
        })
        .catch(error => {
            console.error('Refund order search error:', error);
            showAlert('Lỗi tìm kiếm đơn hàng', 'danger');
        });
}

function displayRefundOrders(orders) {
    const container = document.getElementById('refund-order-results');

    if (orders.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="bi bi-search fs-1"></i>
                <p>Không tìm thấy đơn hàng</p>
            </div>
        `;
        return;
    }

    let html = '';
    orders.forEach(order => {
        html += `
            <div class="card mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${order.code}</h6>
                            <small class="text-muted">
                                ${order.created_at} • ${order.items_count} sản phẩm
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">${formatCurrency(order.grand_total)}</div>
                            <small class="text-success">
                                Có thể hoàn: ${formatCurrency(order.remaining_refundable)}
                            </small>
                        </div>
                        <button class="btn btn-primary btn-sm"
                                onclick="selectRefundOrder('${order.id}')">
                            Chọn
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function selectRefundOrder(orderId) {
    fetch(`/pos/order-details/${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectedRefundOrder = data.data;
                displayRefundItems(data.data.items);
                showAlert(`Đã chọn đơn hàng ${data.data.code}`, 'success');
            } else {
                showAlert('Lỗi tải chi tiết đơn hàng', 'danger');
            }
        })
        .catch(error => {
            console.error('Order details error:', error);
            showAlert('Lỗi tải chi tiết đơn hàng', 'danger');
        });
}

function displayRefundItems(items) {
    const container = document.getElementById('refund-items-list');

    let html = '';
    items.forEach(item => {
        html += `
            <div class="card mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${item.name_snapshot}</h6>
                            <small class="text-muted">SKU: ${item.sku_snapshot}</small>
                            <div class="mt-1">
                                <span class="fw-bold">${formatCurrency(item.unit_price)}</span>
                                <span class="text-muted">x ${item.quantity}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <input type="number"
                                   class="form-control form-control-sm me-2"
                                   style="width: 80px;"
                                   min="0"
                                   max="${item.quantity}"
                                   value="0"
                                   id="refund-qty-${item.id}"
                                   onchange="updateRefundItem(${item.id}, this.value, ${item.unit_price})">
                            <button class="btn btn-outline-primary btn-sm"
                                    onclick="setFullRefund(${item.id}, ${item.quantity}, ${item.unit_price})">
                                Toàn bộ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function updateRefundItem(itemId, quantity, unitPrice) {
    quantity = parseInt(quantity) || 0;

    if (quantity > 0) {
        refundItems[itemId] = {
            original_item_id: itemId,
            quantity: quantity,
            unit_price: unitPrice
        };
    } else {
        delete refundItems[itemId];
    }

    updateRefundSummary();
}

function setFullRefund(itemId, maxQuantity, unitPrice) {
    document.getElementById(`refund-qty-${itemId}`).value = maxQuantity;
    updateRefundItem(itemId, maxQuantity, unitPrice);
}

function updateRefundSummary() {
    const summaryElement = document.getElementById('refund-summary');
    const processBtn = document.getElementById('process-refund');

    const totalItems = Object.keys(refundItems).length;
    const totalAmount = Object.values(refundItems).reduce((sum, item) =>
        sum + (item.quantity * item.unit_price), 0
    );

    if (totalItems > 0) {
        summaryElement.innerHTML = `
            <div class="alert alert-info">
                <strong>Hoàn trả:</strong> ${totalItems} mặt hàng - ${formatCurrency(totalAmount)}
            </div>
        `;
        processBtn.disabled = false;
    } else {
        summaryElement.innerHTML = '';
        processBtn.disabled = true;
    }
}

function processRefund() {
    if (!selectedRefundOrder || Object.keys(refundItems).length === 0) {
        showAlert('Vui lòng chọn sản phẩm hoàn trả', 'warning');
        return;
    }

    const reason = document.getElementById('refund-reason').value;
    const reasonDetail = document.getElementById('refund-reason-detail').value;

    if (!reason) {
        showAlert('Vui lòng chọn lý do hoàn trả', 'warning');
        return;
    }

    const refundData = {
        parent_order_id: selectedRefundOrder.id,
        items: Object.values(refundItems),
        refund_reason: reason,
        refund_reason_detail: reasonDetail,
        payment_method: 'cash_at_counter'
    };

    fetch('/pos/create-refund', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(refundData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Hoàn trả thành công!', 'success');

                // Reset refund state
                selectedRefundOrder = null;
                refundItems = {};
                document.getElementById('refund-order-search').value = '';
                document.getElementById('refund-order-results').innerHTML = '';
                document.getElementById('refund-items-list').innerHTML = '';
                document.getElementById('refund-summary').innerHTML = '';
                document.getElementById('refund-reason').value = '';
                document.getElementById('refund-reason-detail').value = '';

            } else {
                showAlert(data.message || 'Lỗi xử lý hoàn trả', 'danger');
            }
        })
        .catch(error => {
            console.error('Refund processing error:', error);
            showAlert('Lỗi xử lý hoàn trả', 'danger');
        });
}

// ========================================
// UTILITY FUNCTIONS
// ========================================
function formatCurrency(amount) {
    if (isNaN(amount)) return '0';
    return parseInt(amount).toLocaleString('vi-VN');
}

function showAlert(message, type = 'info') {
    // Create alert element
    const alertId = 'alert-' + Date.now();
    const alertHtml = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show position-fixed"
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas fa-${getIconForType(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', alertHtml);

    // Auto remove after 5 seconds
    setTimeout(() => {
        const alertElement = document.getElementById(alertId);
        if (alertElement) {
            alertElement.remove();
        }
    }, 5000);
}

function getIconForType(type) {
    switch (type) {
        case 'success': return 'check-circle';
        case 'danger': return 'exclamation-triangle';
        case 'warning': return 'exclamation-circle';
        case 'info': return 'info-circle';
        default: return 'info-circle';
    }
}

function loadCategories() {
    fetch('/api/products/categories')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                categories = data.data;
                console.log('Categories loaded:', categories.length);
            }
        })
        .catch(error => {
            console.error('Categories loading error:', error);
        });
}

function updatePaymentMethodUI() {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;

    // Update UI based on selected payment method
    if (paymentMethod === 'vnpay') {
        // Show VNPay specific UI
        console.log('VNPay selected');
    } else if (paymentMethod === 'cash_at_counter') {
        // Show cash specific UI
        console.log('Cash selected');
    }
}

// ========================================
// KEYBOARD SHORTCUTS
// ========================================
document.addEventListener('keydown', function (e) {
    // Ignore if input is focused
    if (isInputElementFocused()) return;

    switch (e.key) {
        case 'F1':
            e.preventDefault();
            switchMode('sale');
            break;
        case 'F2':
            e.preventDefault();
            switchMode('refund');
            break;
        case 'F3':
            e.preventDefault();
            document.getElementById('unified-search')?.focus();
            break;
        case 'F4':
            e.preventDefault();
            toggleBarcodeScanner();
            break;
        case 'Escape':
            e.preventDefault();
            hideSearchResults();
            break;
    }
});

// ========================================
// CLEANUP ON PAGE UNLOAD
// ========================================
window.addEventListener('beforeunload', function () {
    if (paymentCheckInterval) {
        clearInterval(paymentCheckInterval);
    }

    if (vnpayPopupWindow && !vnpayPopupWindow.closed) {
        vnpayPopupWindow.close();
    }
});

