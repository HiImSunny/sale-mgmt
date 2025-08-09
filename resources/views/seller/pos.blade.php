@extends('layouts.pos')

@section('content')
<div class="container-fluid px-3">
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-search me-2"></i>
                        Tìm kiếm sản phẩm
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Unified Search Section -->
                    <div class="mb-4">
                        <!-- Search Input with Camera Toggle -->
                        <div class="row mb-3">
                            <div class="col-md-9">
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-lg" id="unified-search"
                                        placeholder="Tìm theo tên, mã SKU, EAN, UPC, mô tả hoặc quét barcode..."
                                        autocomplete="off">
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-camera">
                                        <i class="fas fa-camera" id="camera-icon"></i>
                                        <span class="ms-1 d-none d-sm-inline" id="camera-text">Camera</span>
                                    </button>
                                </div>
                                <div class="spinner-border spinner-border-sm position-absolute end-0 top-50 translate-middle-y me-5"
                                    id="search-loading" style="display: none; z-index: 10;"></div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-lg" id="category-filter">
                                    <option value="">Tất cả danh mục</option>
                                </select>
                            </div>
                        </div>

                        <!-- Camera Section (Initially Hidden) -->
                        <div class="mb-4" id="camera-section" style="display: none;">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="fas fa-video me-2"></i>
                                            Camera đang hoạt động
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-light" id="stop-camera">
                                            <i class="fas fa-times me-1"></i>
                                            Dừng
                                        </button>
                                    </div>
                                </div>

                                <!-- Camera Selection Dropdown -->
                                <div class="card-body pb-2">
                                    <div class="row mb-3">
                                        <div class="col-md-8">
                                            <label class="form-label small">Chọn camera:</label>
                                            <select class="form-select form-select-sm" id="camera-select">
                                                <option value="">Đang tải danh sách camera...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="button" class="btn btn-success btn-sm w-100" id="apply-camera" disabled>
                                                <i class="fas fa-check me-1"></i>
                                                Áp dụng
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Trong camera section, thêm scanning guide -->
                                <div class="card-body pt-0">
                                    <!-- Scan area guide -->
                                    <div class="position-relative">
                                        <div id="interactive" class="viewport">
                                            <!-- Scan guide overlay -->
                                            <div class="scan-guide-overlay">
                                                <div class="scan-frame">
                                                    <div class="scan-corners">
                                                        <div class="corner top-left"></div>
                                                        <div class="corner top-right"></div>
                                                        <div class="corner bottom-left"></div>
                                                        <div class="corner bottom-right"></div>
                                                    </div>
                                                    <div class="scan-line"></div>
                                                </div>
                                                <div class="scan-instructions">
                                                    <p class="mb-1">Đặt mã vạch vào khung</p>
                                                    <small>Giữ camera ổn định</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Search Results -->
                        <div class="position-relative">
                            <div class="dropdown-menu w-100 show" id="search-results"
                                style="display: none; max-height: 400px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Mẹo:</strong>
                        Gõ tên sản phẩm hoặc mã để tìm nhanh •
                        Bấm <i class="fas fa-camera"></i> để quét barcode •
                        Click vào kết quả để thêm vào giỏ
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Shopping Cart -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        Giỏ hàng
                        <span class="badge bg-secondary ms-2" id="cart-count">0</span>
                    </h4>
                </div>
                <div class="card-body">
                    <div id="cart-items">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                            <p class="mb-0">Chưa có sản phẩm nào</p>
                        </div>
                    </div>

                    <hr>

                    <!-- Payment Method -->
                    <div class="mb-3">
                        <label class="form-label">Phương thức thanh toán:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash_at_counter" checked>
                            <label class="form-check-label" for="cash">
                                <i class="fas fa-money-bill me-1"></i>
                                Tiền mặt
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="vnpay" value="vnpay">
                            <label class="form-check-label" for="vnpay">
                                <i class="fas fa-credit-card me-1"></i>
                                VNPAY
                            </label>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <strong id="subtotal">0 ₫</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h5">Tổng cộng:</span>
                            <strong class="h5" id="total">0 ₫</strong>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary btn-lg" id="confirm-payment" disabled>
                                Xác nhận đã thanh toán
                            </button>
                            <button type="button" class="btn btn-success btn-lg" id="complete-order" style="display: none;">
                                Hoàn tất & In hóa đơn
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="clear-cart">
                                Xóa giỏ hàng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="variantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chọn biến thể sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="variant-list">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
<script>
    // ========================================
    // GLOBAL VARIABLES
    // ========================================
    let cart = [];
    let currentOrder = null;
    let searchTimeout;
    let searchCache = {};
    let cameraActive = false;
    let cameraDevices = [];
    let selectedCameraId = null;
    let permissionRequested = false;

    // ========================================
    // INITIALIZATION
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        initializeEventListeners();
        loadCategories();
        initializeUnifiedSearch();
        loadCameraDevices();
        initializeKeyboardShortcuts();
    });

    function initializeEventListeners() {
        // Cart actions
        document.getElementById('confirm-payment').addEventListener('click', confirmPayment);
        document.getElementById('complete-order').addEventListener('click', completeOrder);
        document.getElementById('clear-cart').addEventListener('click', clearCart);
    }

    function initializeUnifiedSearch() {
        const searchInput = document.getElementById('unified-search');
        const categoryFilter = document.getElementById('category-filter');
        const toggleCamera = document.getElementById('toggle-camera');
        const stopCamera = document.getElementById('stop-camera');
        const cameraSelect = document.getElementById('camera-select');
        const applyCamera = document.getElementById('apply-camera');

        // Toggle camera
        toggleCamera.addEventListener('click', async function() {
            if (cameraActive) {
                stopCameraMode();
            } else {
                // Request permission first time
                if (!permissionRequested) {
                    permissionRequested = true;
                    const hasPermission = await requestCameraPermission();
                    if (!hasPermission) {
                        return;
                    }
                }
                startCameraMode();
            }
        });

        // Stop camera
        stopCamera.addEventListener('click', stopCameraMode);

        // Camera selection change
        if (cameraSelect) {
            cameraSelect.addEventListener('change', function() {
                selectedCameraId = this.value;
                document.getElementById('apply-camera').disabled = !selectedCameraId;

                // Stop current camera if running
                if (cameraActive) {
                    stopQuaggaOnly();
                }
            });
        }

        // Apply camera selection
        if (applyCamera) {
            applyCamera.addEventListener('click', function() {
                if (selectedCameraId) {
                    initializeCameraWithDevice(selectedCameraId);
                }
            });
        }

        // Unified search (keyboard input)
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(searchTimeout);

            if (query.length < 2) {
                hideSearchResults();
                return;
            }

            // Debounce 300ms
            searchTimeout = setTimeout(() => {
                performSearch(query, categoryFilter.value);
            }, 300);
        });

        // Category filter
        categoryFilter.addEventListener('change', function() {
            const query = searchInput.value.trim();
            if (query.length >= 2) {
                performSearch(query, this.value);
            }
        });

        // Hide results when click outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#unified-search') && !e.target.closest('#search-results')) {
                hideSearchResults();
            }
        });

        // Show results when focus
        searchInput.addEventListener('focus', function() {
            const query = this.value.trim();
            if (query.length >= 2) {
                showSearchResults();
            }
        });

        // Enter key để tìm kiếm ngay
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.value.trim();
                if (query.length >= 1) {
                    performSearch(query, categoryFilter.value);
                }
            }
        });
    }

    // ========================================
    // CAMERA FUNCTIONALITY
    // ========================================
    async function loadCameraDevices() {
        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
                console.warn('Camera enumeration not supported');
                return;
            }

            const devices = await navigator.mediaDevices.enumerateDevices();
            cameraDevices = devices.filter(device => device.kind === 'videoinput');

            console.log('Available cameras:', cameraDevices);

            const cameraSelect = document.getElementById('camera-select');
            if (!cameraSelect) return;

            cameraSelect.innerHTML = '';

            if (cameraDevices.length === 0) {
                cameraSelect.innerHTML = '<option value="">Không tìm thấy camera nào</option>';
                return;
            }

            cameraSelect.innerHTML = '<option value="">Chọn camera...</option>';

            cameraDevices.forEach((device, index) => {
                const option = document.createElement('option');
                option.value = device.deviceId;

                let label = device.label;
                if (!label || label.trim() === '') {
                    label = `Camera ${index + 1}`;
                }

                // Highlight special cameras
                if (label.toLowerCase().includes('ivcam') ||
                    label.toLowerCase().includes('virtual') ||
                    label.toLowerCase().includes('obs') ||
                    label.toLowerCase().includes('droidcam')) {
                    label += ' (Recommended)';
                    option.setAttribute('data-recommended', 'true');
                }

                option.textContent = label;
                cameraSelect.appendChild(option);
            });

            // Auto-select recommended camera
            const recommendedOption = cameraSelect.querySelector('[data-recommended="true"]');
            if (recommendedOption) {
                recommendedOption.selected = true;
                selectedCameraId = recommendedOption.value;
                const applyBtn = document.getElementById('apply-camera');
                if (applyBtn) applyBtn.disabled = false;
            }

        } catch (error) {
            console.error('Error loading cameras:', error);
            const cameraSelect = document.getElementById('camera-select');
            if (cameraSelect) {
                cameraSelect.innerHTML = '<option value="">Lỗi tải camera</option>';
            }
        }
    }

    async function requestCameraPermission() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: true
            });
            stream.getTracks().forEach(track => track.stop());
            await loadCameraDevices();
            return true;
        } catch (error) {
            console.error('Permission denied:', error);
            showAlert('Cần cấp quyền truy cập camera để sử dụng tính năng này', 'warning');
            return false;
        }
    }

    function startCameraMode() {
        const cameraSection = document.getElementById('camera-section');
        const cameraIcon = document.getElementById('camera-icon');
        const cameraText = document.getElementById('camera-text');
        const searchInput = document.getElementById('unified-search');

        cameraSection.style.display = 'block';
        cameraIcon.className = 'fas fa-video';
        if (cameraText) cameraText.textContent = 'Đang quét...';
        searchInput.placeholder = 'Đang quét barcode... hoặc gõ để tìm kiếm';

        // Load cameras if not loaded
        if (cameraDevices.length === 0) {
            loadCameraDevices();
        }

        // If a camera is selected, start it
        if (selectedCameraId) {
            initializeCameraWithDevice(selectedCameraId);
        }
    }

    function initializeCameraWithDevice(deviceId) {
        console.log('Initializing camera with device:', deviceId);

        const applyBtn = document.getElementById('apply-camera');
        if (applyBtn) {
            applyBtn.disabled = true;
            applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang khởi động...';
        }

        if (cameraActive) {
            stopQuaggaOnly();
        }

        // Enhanced constraints for better scanning
        const constraints = {
            width: {
                ideal: 1280,
                min: 640
            }, // ← Tăng resolution
            height: {
                ideal: 720,
                min: 480
            },
            deviceId: {
                exact: deviceId
            },
            facingMode: {
                ideal: "environment"
            },
            focusMode: {
                ideal: "continuous"
            }, // ← Auto focus
            exposureMode: {
                ideal: "continuous"
            }
        };

        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                constraints: constraints,
                target: document.querySelector('#interactive'),
                area: { // ← Giới hạn scan area
                    top: "20%",
                    right: "20%",
                    left: "20%",
                    bottom: "20%"
                }
            },
            locator: {
                patchSize: "medium",
                halfSample: false, // ← Tăng chất lượng
                showCanvas: true, // ← Hiển thị scan area
                showPatches: false,
                showFoundPatches: false,
                showSkeleton: false,
                showLabels: false,
                showPatchLabels: false,
                showBoundingBox: true,
                boxFromPatches: {
                    showTransformed: true,
                    showTransformedBox: true,
                    showBB: true
                }
            },
            numOfWorkers: 4, // ← Tăng workers để xử lý nhanh hơn
            decoder: {
                readers: [
                    "code_128_reader", // ← Ưu tiên Code128 (SKU)
                    "ean_reader",
                    "ean_8_reader",
                    "code_39_reader"
                    // Bỏ UPC readers nếu không cần thiết
                ]
            },
            locate: true,
            frequency: 5, // ← Giảm tần suất scan để tránh spam
            debug: {
                showCanvas: true,
                showPatches: false,
                showFoundPatches: false,
                showSkeleton: false,
                showLabels: false,
                showPatchLabels: false,
                showBoundingBox: true,
                boxFromPatches: {
                    showTransformed: true,
                    showTransformedBox: true,
                    showBB: true
                }
            }
        }, function(err) {
            // Reset button state
            if (applyBtn) {
                applyBtn.disabled = false;
                applyBtn.innerHTML = '<i class="fas fa-check me-1"></i>Áp dụng';
            }

            if (err) {
                console.error('Quagga init error:', err);
                showAlert('Không thể khởi động camera: ' + err.message, 'danger');
                return;
            }

            console.log("Camera started successfully");
            Quagga.start();
            cameraActive = true;

            showAlert('Camera đã sẵn sàng quét mã vạch!', 'success');

            if (applyBtn) {
                applyBtn.innerHTML = '<i class="fas fa-sync me-1"></i>Đổi camera';
            }
        });

        // Enhanced barcode detection với validation
        let lastScannedCode = '';
        let lastScannedTime = 0;
        let scannedCount = {};

        Quagga.onDetected(function(result) {
            const code = result.codeResult.code;
            const confidence = result.codeResult.decodedCodes
                .map(d => d.confidence)
                .reduce((a, b) => Math.max(a, b), 0);

            console.log("Detected:", code, "Confidence:", confidence);

            // Validation 1: Minimum confidence threshold
            if (confidence < 80) {
                console.log("Low confidence, ignoring:", confidence);
                return;
            }

            // Validation 2: Prevent duplicate scans
            const currentTime = Date.now();
            if (code === lastScannedCode && (currentTime - lastScannedTime) < 2000) {
                console.log("Duplicate scan ignored");
                return;
            }

            // Validation 3: Multiple detection confirmation
            if (!scannedCount[code]) {
                scannedCount[code] = 1;
                setTimeout(() => delete scannedCount[code], 3000); // Clean after 3s
                console.log("First detection, waiting for confirmation");
                return;
            } else {
                scannedCount[code]++;
            }

            // Only accept if detected multiple times
            if (scannedCount[code] < 2) {
                console.log("Waiting for more confirmations:", scannedCount[code]);
                return;
            }

            // Validation 4: Basic format check
            if (!isValidBarcode(code)) {
                console.log("Invalid barcode format:", code);
                return;
            }

            console.log("✅ Valid scan confirmed:", code);

            // Update tracking
            lastScannedCode = code;
            lastScannedTime = currentTime;

            // Fill search input
            document.getElementById('unified-search').value = code;

            // Search for product
            searchProductByCode(code);

            // Visual feedback
            showAlert(`✅ Quét thành công: ${code}`, 'success');

            // Auto-hide camera after successful scan (optional)
            // setTimeout(() => stopCameraMode(), 2000);
        });
    }

    // Enhanced barcode validation function
    function isValidBarcode(code) {
        // Remove invalid characters
        const cleanCode = code.replace(/[^A-Z0-9\-]/g, '');

        // Basic length checks
        if (cleanCode.length < 3 || cleanCode.length > 20) {
            return false;
        }

        // Pattern checks for common formats
        const patterns = [
            /^[A-Z]{2,5}\d{3}\-[A-Z0-9]{2,5}\-[A-Z0-9]{1,3}$/, // SKU: ATN001-DO-M
            /^\d{8,13}$/, // EAN/UPC: 8-13 digits
            /^[A-Z0-9]{3,20}$/ // General alphanumeric
        ];

        return patterns.some(pattern => pattern.test(cleanCode));
    }


    function stopQuaggaOnly() {
        if (cameraActive) {
            Quagga.stop();
            cameraActive = false;
        }
    }

    function stopCameraMode() {
        const cameraSection = document.getElementById('camera-section');
        const cameraIcon = document.getElementById('camera-icon');
        const cameraText = document.getElementById('camera-text');
        const searchInput = document.getElementById('unified-search');

        stopQuaggaOnly();

        cameraSection.style.display = 'none';
        cameraIcon.className = 'fas fa-camera';
        if (cameraText) cameraText.textContent = 'Camera';
        searchInput.placeholder = 'Tìm theo tên, mã SKU, EAN, UPC, mô tả hoặc quét barcode...';

        // Reset viewport
        document.getElementById('interactive').innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100 bg-dark text-white">
                <div class="text-center">
                    <div class="spinner-border mb-3" role="status">
                        <span class="visually-hidden">Đang khởi động camera...</span>
                    </div>
                    <p class="mb-0">Chọn camera và bấm "Áp dụng"</p>
                    <small class="text-muted">Hãy đưa mã vạch vào khung hình</small>
                </div>
            </div>
        `;
    }

    // ========================================
    // SEARCH FUNCTIONALITY
    // ========================================
    function loadCategories() {
        fetch('/api/categories')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const categorySelect = document.getElementById('category-filter');
                    data.data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        categorySelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading categories:', error));
    }

    function performSearch(query, category = '') {
        const loadingSpinner = document.getElementById('search-loading');

        const cacheKey = `${query}-${category}`;
        if (searchCache[cacheKey]) {
            displaySearchResults(searchCache[cacheKey]);
            return;
        }

        loadingSpinner.style.display = 'block';

        const params = new URLSearchParams({
            q: query,
            limit: 15
        });

        if (category) {
            params.append('category', category);
        }

        fetch(`/api/products/search?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                loadingSpinner.style.display = 'none';

                if (data.success) {
                    searchCache[cacheKey] = data.data;
                    displaySearchResults(data.data);
                } else {
                    displaySearchResults([]);
                }
            })
            .catch(error => {
                loadingSpinner.style.display = 'none';
                console.error('Search error:', error);
                displaySearchResults([]);
            });
    }

    function searchProductByCode(code) {
        fetch(`/api/products/by-code?code=${encodeURIComponent(code)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    handleProductFound(data);
                } else {
                    showAlert(`Không tìm thấy sản phẩm với mã: ${code}`, 'warning');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Có lỗi xảy ra khi tìm sản phẩm', 'danger');
            });
    }

    function handleProductFound(data) {
        if (data.type === 'variant') {
            addToCart(data.data);
        } else if (data.type === 'product_with_variants') {
            showVariantModal(data.data);
        } else if (data.type === 'product') {
            addToCart(data.data);
        }
    }

    function displaySearchResults(results) {
        const resultsContainer = document.getElementById('search-results');
        const resultsHeader = document.getElementById('results-header');

        if (results.length === 0) {
            resultsContainer.innerHTML = `
            <div class="dropdown-item-text text-muted text-center py-4">
                <i class="fas fa-search-minus fa-2x mb-2"></i>
                <div>Không tìm thấy sản phẩm nào</div>
                <small class="text-muted">Thử từ khóa khác hoặc quét mã vạch</small>
            </div>
        `;
            if (resultsHeader) resultsHeader.style.display = 'none';
        } else {
            let html = '';
            results.forEach(product => {
                const stockStatus = product.stock > 10 ? 'success' :
                    product.stock > 0 ? 'warning' : 'danger';
                const stockText = product.stock > 10 ? 'Còn hàng' :
                    product.stock > 0 ? `Còn ${product.stock}` : 'Hết hàng';

                const priceDisplay = product.sale_price ?
                    `<small class="text-decoration-line-through text-muted">${formatCurrency(product.price)}</small><br>
                 <strong class="text-danger">${formatCurrency(product.final_price)}</strong>` :
                    `<strong class="text-primary">${formatCurrency(product.final_price)}</strong>`;

                // ✅ Sử dụng pattern error handling giống như lúc trước
                html += `
                <div class="dropdown-item search-result-item ${product.stock <= 0 ? 'disabled' : ''}" 
                     data-product='${JSON.stringify(product)}'>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            ${product.thumbnail ? 
                                `<img src="${product.thumbnail}" 
                                     class="rounded" 
                                     width="50" 
                                     height="50" 
                                     style="object-fit: cover; border: 1px solid #dee2e6;" 
                                     onerror="this.onerror=null; this.src='/images/no-image.png'; if(this.src==='/images/no-image.png'){this.parentElement.innerHTML='<div class=\\"rounded bg-secondary d-flex align-items-center justify-content-center text-white\\" style=\\"width: 50px; height: 50px;\\"><i class=\\"fas fa-cube\\"></i></div>';}"
                                     alt="${product.variant_name || product.name}">` :
                                `<div class="rounded bg-secondary d-flex align-items-center justify-content-center text-white" 
                                     style="width: 50px; height: 50px; border: 1px solid #6c757d;">
                                    <i class="fas fa-cube"></i>
                                 </div>`
                            }
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-3" style="min-width: 0;">
                                    <h6 class="mb-1 fw-bold text-truncate" 
                                       style="max-width: 250px;" 
                                       title="${product.variant_name || product.name}">
                                        ${product.variant_name || product.name}
                                    </h6>
                                    <div class="text-muted small">
                                        <i class="fas fa-barcode me-1"></i>
                                        <span class="fw-medium">${product.sku}</span>
                                        ${product.categories ? 
                                            `<span class="text-secondary"> • ${product.categories}</span>` : 
                                            ''
                                        }
                                    </div>
                                    ${product.attributes ? 
                                        `<div class="mt-1">
                                            <small class="badge bg-info bg-opacity-25 text-info">
                                                ${product.attributes}
                                            </small>
                                         </div>` : 
                                        ''
                                    }
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
            if (resultsHeader) resultsHeader.style.display = 'block';

            // Add click handlers
            resultsContainer.querySelectorAll('.search-result-item:not(.disabled)').forEach(item => {
                item.addEventListener('click', function() {
                    const productData = JSON.parse(this.getAttribute('data-product'));
                    addToCart(productData);
                    hideSearchResults();
                    document.getElementById('unified-search').value = '';
                });
            });
        }

        showSearchResults();
    }


    function showSearchResults() {
        document.getElementById('search-results').style.display = 'block';
    }

    function hideSearchResults() {
        document.getElementById('search-results').style.display = 'none';
    }

    // ========================================
    // VARIANT SELECTION MODAL
    // ========================================
    function showVariantModal(product) {
        const modalBody = document.getElementById('variant-list');
        if (!modalBody) return;

        let html = `<h6>${product.name}</h6>`;
        html += '<div class="list-group">';

        product.variants.forEach(variant => {
            const attrs = variant.attributes.map(attr =>
                `${attr.attribute_name}: ${attr.value}`
            ).join(', ');

            html += `
                <button type="button" class="list-group-item list-group-item-action" 
                        onclick="selectVariant(${variant.id}, '${variant.sku}', '${product.name} (${attrs})', ${variant.final_price}, '${product.thumbnail}')">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${attrs}</h6>
                            <small>SKU: ${variant.sku} | Tồn: ${variant.stock}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary">${formatCurrency(variant.final_price)}</span>
                        </div>
                    </div>
                </button>
            `;
        });

        html += '</div>';
        modalBody.innerHTML = html;

        const modal = new bootstrap.Modal(document.getElementById('variantModal'));
        modal.show();
    }

    function selectVariant(variantId, sku, name, price, thumbnail) {
        const variantData = {
            id: variantId,
            name: name,
            sku: sku,
            final_price: price,
            thumbnail: thumbnail,
            type: 'variant'
        };

        addToCart(variantData);

        const modal = bootstrap.Modal.getInstance(document.getElementById('variantModal'));
        if (modal) modal.hide();
    }

    // ========================================
    // CART MANAGEMENT
    // ========================================
    function addToCart(product) {
        const existingItem = cart.find(item =>
            (product.type === 'variant' ? item.variant_id === product.id : item.product_id === product.id)
        );

        if (existingItem) {
            existingItem.quantity += 1;
            existingItem.line_total = existingItem.quantity * existingItem.unit_price;
        } else {
            const cartItem = {
                id: Date.now(),
                product_id: product.type === 'product' ? product.id : null,
                variant_id: product.type === 'variant' ? product.id : null,
                name: product.name || product.variant_name,
                sku: product.sku,
                unit_price: product.final_price,
                quantity: 1,
                line_total: product.final_price,
                thumbnail: product.thumbnail
            };

            cart.push(cartItem);
        }

        updateCartDisplay();
        showAlert(`Đã thêm "${product.name || product.variant_name}" vào giỏ hàng`, 'success');

        document.getElementById('unified-search').value = '';
        hideSearchResults();
    }

    function removeFromCart(cartId) {
        cart = cart.filter(item => item.id !== cartId);
        updateCartDisplay();
    }

    function updateCartQuantity(cartId, newQuantity) {
        const item = cart.find(item => item.id === cartId);
        if (item && newQuantity > 0) {
            item.quantity = parseInt(newQuantity);
            item.line_total = item.quantity * item.unit_price;
            updateCartDisplay();
        }
    }

    function updateCartDisplay() {
        const cartItemsDiv = document.getElementById('cart-items');
        const cartCount = document.getElementById('cart-count');

        if (cart.length === 0) {
            cartItemsDiv.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                    <p class="mb-0">Chưa có sản phẩm nào</p>
                </div>
            `;
            cartCount.textContent = '0';
            document.getElementById('confirm-payment').disabled = true;
        } else {
            let html = '';
            cart.forEach(item => {
                html += `
                    <div class="cart-item border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="flex-grow-1">
                                <small class="text-muted">${item.sku}</small>
                                <h6 class="mb-1 small">${item.name}</h6>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="removeFromCart(${item.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="input-group input-group-sm" style="width: 100px;">
                                <input type="number" class="form-control" value="${item.quantity}" min="1"
                                       onchange="updateCartQuantity(${item.id}, this.value)">
                            </div>
                            <div class="text-end">
                                <div class="small text-muted">${formatCurrency(item.unit_price)} x ${item.quantity}</div>
                                <div class="fw-bold">${formatCurrency(item.line_total)}</div>
                            </div>
                        </div>
                    </div>
                `;
            });

            cartItemsDiv.innerHTML = html;
            cartCount.textContent = cart.length;
            document.getElementById('confirm-payment').disabled = false;
        }

        // Update totals
        const subtotal = cart.reduce((sum, item) => sum + item.line_total, 0);
        document.getElementById('subtotal').textContent = formatCurrency(subtotal);
        document.getElementById('total').textContent = formatCurrency(subtotal);
    }

    function clearCart() {
        cart = [];
        currentOrder = null;
        updateCartDisplay();

        document.getElementById('confirm-payment').style.display = 'block';
        document.getElementById('complete-order').style.display = 'none';
        document.getElementById('confirm-payment').disabled = true;

        document.getElementById('unified-search').value = '';
        hideSearchResults();
    }

    // ========================================
    // ORDER PROCESSING
    // ========================================
    function confirmPayment() {
        if (cart.length === 0) {
            showAlert('Giỏ hàng trống', 'warning');
            return;
        }

        const orderData = {
            items: cart.map(item => ({
                variant_id: item.variant_id,
                quantity: item.quantity,
                unit_price: item.unit_price
            })).filter(item => item.variant_id),
            payment_method: document.querySelector('input[name="payment_method"]:checked').value
        };

        fetch('/seller/pos/create-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentOrder = data.data;
                    showAlert('Đơn hàng đã được tạo thành công!', 'success');

                    document.getElementById('confirm-payment').style.display = 'none';
                    document.getElementById('complete-order').style.display = 'block';
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Có lỗi xảy ra khi tạo đơn hàng', 'danger');
            });
    }

    function completeOrder() {
        if (!currentOrder) return;

        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

        fetch(`/seller/pos/confirm-payment/${currentOrder.order_id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    payment_method: paymentMethod
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Thanh toán thành công!', 'success');

                    if (data.data.invoice_url) {
                        window.open(data.data.invoice_url, '_blank');
                    }

                    clearCart();
                    currentOrder = null;
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Có lỗi xảy ra khi xác nhận thanh toán', 'danger');
            });
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const container = document.querySelector('.container-fluid');
        container.insertBefore(alertDiv, container.firstChild);

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    function focusSearch() {
        const searchInput = document.getElementById('unified-search');
        searchInput.focus();
        searchInput.select();
    }

    setInterval(() => {
        searchCache = {};
        console.log('Search cache cleared');
    }, 5 * 60 * 1000); // Clear every 5 minutes

    window.removeFromCart = removeFromCart;
    window.updateCartQuantity = updateCartQuantity;
    window.selectVariant = selectVariant;
    window.focusSearch = focusSearch;
</script>
@endpush