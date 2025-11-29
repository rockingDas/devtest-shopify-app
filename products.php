<?php

    include_once( "includes/config.php" );
    include_once( "includes/functions.php" );
    include_once( "includes/mysql_connect.php" );
    include_once( "includes/shopify.php" );

    $shopify = new Shopify();
    $parameters = $_GET;

    include_once("includes/check_token.php");

    include_once("includes/header.php");
?>

<?php

    $shop = $_GET['shop'] ?? '';
    $host = $_GET['host'] ?? '';
    $baseUrl = $_SERVER['PHP_SELF'];

    $title = $_GET['title'] ?? '';
    $status = $_GET['status'] ?? '';
    $limit = $_GET['limit'] ?? 5;

    $cursor = null;
    $direction = 'after'; // default is forward
    if(isset($_GET['after']) && $_GET['after']){
        $cursor = $_GET['after'];
        $direction = 'after';
    }
    if(isset($_GET['before']) && $_GET['before']){
        $cursor = $_GET['before'];
        $direction = 'before';
    }

    $response = $shopify->fetchProductsGraphQL($cursor, $direction, $limit, $title, $status);
    // echo "<pre>";
    // print_r($response);
    // echo "</pre>";
    if( !is_null($response) && array_key_exists( "errors", $response ) ){
        header("Location: install.php?shop=". $_GET['shop']);
        exit();
    }

    // die();
    $edges = $response['data']['products']['edges'];
    $pageInfo = $response['data']['products']['pageInfo'];
?>

<style>
    .pagination .button-group {
        display: flex;
        margin: 0;
        gap: 10px;
        justify-content: center;
    }
    /* Basic modal styling */
    .modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 10px;
        width: 60%;
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
        overflow: auto;
        height: 80%;
        margin: 5% auto;
    }
    .modal-header {
      font-weight: bold;
      margin-bottom: 15px;
    }
    .close-btn {
      float: right;
      cursor: pointer;
      color: red;
      width: 25px !important;
      height: 25px !important;
    }


    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background: #f6f6f7; padding: 20px; }
    .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 30px; }
    h1 { color: #202223; margin-bottom: 24px; font-size: 24px; }
    .form-section { margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #e1e3e5; }
    .form-section:last-child { border-bottom: none; }
    .section-title { font-size: 16px; font-weight: 600; color: #202223; margin-bottom: 16px; }
    .form-group { margin-bottom: 16px; }
    label { display: block; font-size: 13px; font-weight: 500; color: #202223; margin-bottom: 6px; }
    input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 10px 12px; border: 1px solid #c9cccf; border-radius: 4px; font-size: 14px; font-family: inherit; }
    input:focus, textarea:focus, select:focus { outline: none; border-color: #005bd3; box-shadow: 0 0 0 1px #005bd3; }
    textarea { resize: vertical; min-height: 100px; }
    .tag-input-container { display: flex; flex-wrap: wrap; gap: 8px; padding: 8px; border: 1px solid #c9cccf; border-radius: 4px; min-height: 42px; }
    .tag { background: #e4e5e7; padding: 4px 8px; border-radius: 4px; display: flex; align-items: center; gap: 6px; font-size: 13px; }
    .tag-remove { cursor: pointer; color: #6d7175; font-weight: bold; }
    .tag-input { border: none; flex: 1; min-width: 120px; outline: none; font-size: 14px; }
    .image-upload-area { border: 2px dashed #c9cccf; border-radius: 4px; padding: 24px; text-align: center; cursor: pointer; transition: all 0.2s; }
    .image-upload-area:hover { border-color: #005bd3; background: #f6f6f7; }
    .image-preview-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; margin-top: 12px; }
    .image-preview { position: relative; aspect-ratio: 1; border-radius: 4px; overflow: hidden; border: 1px solid #e1e3e5; }
    .image-preview img { width: 100%; height: 100%; object-fit: cover; }
    .image-remove {
    position: absolute;top: 4px;right: 4px;background: rgba(0,0,0,0.7);color: white;border: none;border-radius: 50%;min-width: 0;min-height: 0;width: 30px;height: 30px;cursor: pointer;font-size: 17px;padding: 0;}
    .variant-row { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 40px; gap: 12px; align-items: end; margin-bottom: 12px; padding: 12px; background: #f6f6f7; border-radius: 4px; }
    .add-variant-btn, .submit-btn { background: #008060; color: white; border: none; padding: 10px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; }
    .add-variant-btn:hover, .submit-btn:hover { background: #006e52; }
    .submit-btn { width: 100%; padding: 12px; font-size: 15px; margin-top: 24px; }
    .remove-variant-btn { background: #d72c0d;color: white;border: none;width: 30px;height: 30px;border-radius: 4px;cursor: pointer;font-size: 18px;border-radius: 50px;margin-bottom: 60px;padding: 0;min-height: 0;min-width: 0; }
    .input-hint { font-size: 12px; color: #6d7175; margin-top: 4px; }
    .two-column { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
</style>

    <section>
        <div class="card">

            <button id="openModalBtn">Create Product</button>

            <div id="customModal" class="modal">
                <input type="hidden" name="modal_status" id="modal_status">
                <div class="modal-content">
                    <div class="modal-header">
                    Create New Product
                    <span class="close-btn remove-variant-btn" style="text-align: center;">&times;</span>
                    </div>

                    <form id="productForm">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <div class="section-title">Basic Information</div>
                            
                            <div class="form-group">
                                <label for="productTitle">Product Title *</label>
                                <input type="text" id="productTitle" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="productDescription">Description</label>
                                <textarea id="productDescription" placeholder="Enter product description..."></textarea>
                            </div>
                        </div>

                        <!-- Organization -->
                        <div class="form-section">
                            <div class="section-title">Organization</div>
                            
                            <div class="two-column">
                                <div class="form-group">
                                    <label for="productVendor">Vendor</label>
                                    <input type="text" id="productVendor" value="Suprakash Brand">
                                </div>
                                
                                <div class="form-group">
                                    <label for="productType">Product Type</label>
                                    <select id="productType">
                                        <option value="">Select type</option>
                                        <option value="Clothing">Clothing</option>
                                        <option value="Electronics">Electronics</option>
                                        <option value="Home & Garden">Home & Garden</option>
                                        <option value="Sports">Sports</option>
                                        <option value="Books">Books</option>
                                        <option value="General">General</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="productCategory">Category</label>
                                <select id="productCategory">
                                    <!-- <option value="">Select category</option>
                                    <option value="gid://shopify/TaxonomyCategory/aa-1">Apparel & Accessories</option>
                                    <option value="gid://shopify/TaxonomyCategory/aa-2">Electronics</option>
                                    <option value="gid://shopify/TaxonomyCategory/aa-3">Home & Garden</option>
                                    <option value="gid://shopify/TaxonomyCategory/aa-4">Sporting Goods</option> -->
                                </select>
                                <div class="input-hint">Optional: Select a product category</div>
                            </div>
                            
                            <div class="form-group">
                                <label>Tags</label>
                                <div class="tag-input-container" id="tagContainer">
                                    <input type="text" class="tag-input" id="tagInput" placeholder="Add tags...">
                                </div>
                                <div class="input-hint">Press Enter to add tags</div>
                            </div>
                        </div>

                        <!-- Images -->
                        <div class="form-section">
                            <div class="section-title">Product Images</div>
                            
                            <div class="image-upload-area" id="imageUploadArea">
                                <p>📷 Click to add images or enter image URLs</p>
                                <input type="file" id="imageFileInput" accept="image/*" multiple style="display: none;">
                            </div>
                            
                            <div class="form-group" style="margin-top: 12px;">
                                <label for="imageUrlInput">Or enter image URL</label>
                                <input type="text" id="imageUrlInput" placeholder="https://example.com/image.jpg">
                                <div class="input-hint">Press Enter to add image URL</div>
                            </div>
                            
                            <div class="image-preview-container" id="imagePreviewContainer"></div>
                        </div>

                        <!-- Variants -->
                        <div class="mb-6 pb-6 border-b">
                            <h2 class="text-lg font-semibold mb-4">Variants</h2>
                            
                            <!-- Variant Toggle -->
                            <div class="form-group mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="hasVariants">
                                    <span class="ml-2">This product has multiple options, like different sizes or colors</span>
                                </label>
                            </div>
                            
                            <!-- Base Price/SKU (always visible) -->
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Price * <span class="text-muted">(Base price for all variants)</span></label>
                                    <input type="number" id="basePrice" class="form-control" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">SKU <span class="text-muted">(Base SKU)</span></label>
                                    <input type="text" id="baseSKU" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Inventory <span class="text-muted">(Base inventory)</span></label>
                                    <input type="number" id="baseInventory" class="form-control" value="0">
                                </div>
                            </div>
                            
                            <!-- Options Section -->
                            <div id="optionsSection" style="display: none;">
                                <h3 class="text-md font-semibold mb-3">Options</h3>
                                <div id="optionsContainer">
                                    <div class="form-group mb-4 option-group" data-option="1">
                                        <label class="form-label">Option name</label>
                                        <input type="text" class="option-name form-control mb-2" placeholder="e.g., Size, Color">
                                        <label class="form-label">Option values</label>
                                        <input type="text" class="option-values form-control" placeholder="Separate with comma (e.g., Small, Medium, Large)">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-secondary mb-4" id="addOptionBtn">+ Add another option</button>
                                
                                <button type="button" class="btn btn-primary mb-4" id="generateVariantsBtn">Generate Variants</button>
                            </div>
                            
                            <!-- Variants Table -->
                            <div id="variantsListContainer" style="display: none;">
                                <h3 class="text-md font-semibold mb-3">Variant Details</h3>
                                <table class="table" style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="border-bottom: 2px solid #e1e3e5;">
                                            <th style="padding: 8px;">Variant</th>
                                            <th style="padding: 8px;">Price</th>
                                            <th style="padding: 8px;">SKU</th>
                                            <th style="padding: 8px;">Inventory</th>
                                            <th style="padding: 8px;">Images</th>
                                        </tr>
                                    </thead>
                                    <tbody id="variantsTableBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Publishing -->
                        <div class="mb-6 pb-6 border-b">
                            <div class="section-title">Publishing</div>
                            
                            <div class="two-column">
                            <div class="form-group">
                                <label class="form-label">Sales Channels</label>
                                <div id="publicationsContainer">
                                    <p class="text-muted">Loading channels...</p>
                                </div>
                            </div>
                                
                                <div class="form-group">
                                    <label for="productStatus" class="form-label">Product Status</label>
                                    <select id="productStatus" class="form-select">
                                        <option value="ACTIVE">Active</option>
                                        <option value="DRAFT">Draft</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="submit-btn">Create Product</button>
                    </form>

                </div>
            </div>

        </div>
    </section>

    <section>
        <div class="full-width">
            <form method="get" action="<?= $baseUrl ?>">
                <input type="hidden" name="shop" value="<?php echo htmlspecialchars($shop); ?>">
                <input type="hidden" name="host" value="<?php echo htmlspecialchars($host); ?>">
                <article>
                    <div class="card columns three">
                        <input type="text" name="title" placeholder="Search by title" value="<?php echo htmlspecialchars($_GET['title'] ?? ''); ?>">
                    </div>
                    <div class="card columns three">
                        <select name="status">
                            <option value="">-- Status --</option>
                            <option value="ACTIVE" <?php if(($_GET['status'] ?? '')==='ACTIVE') echo 'selected'; ?>>Active</option>
                            <option value="DRAFT" <?php if(($_GET['status'] ?? '')==='DRAFT') echo 'selected'; ?>>Draft</option>
                            <option value="ARCHIVED" <?php if(($_GET['status'] ?? '')==='ARCHIVED') echo 'selected'; ?>>Archived</option>
                        </select>
                    </div>
                    <div class="card columns three">
                        <select name="limit">
                            <option value="5" <?php if(($_GET['limit'] ?? '')=='5') echo 'selected'; ?>>5</option>
                            <option value="10" <?php if(($_GET['limit'] ?? '')=='10') echo 'selected'; ?>>10</option>
                            <option value="20" <?php if(($_GET['limit'] ?? '')=='20') echo 'selected'; ?>>20</option>
                        </select>
                    </div>
                    <div class="card columns three">
                        <button class="full-width" type="submit">Filter</button>
                    </div>
                </article>
            </form>
        </div>
    </section>

    <section>
        <table>
            <thead>
                <tr>
                <th colspan="2">Product</th>
                <th>Status</th>
                <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach( $edges as $edge ){
                    $p = $edge['node'];
                    $images = count($p['images']) > 0 ? $p['images']['edges'][0]['node']['url'] : "";
                ?>
                    <tr>
                        <td><img width="100" height="100" alt="" src="<?= $images ?>"></td>
                        <td><?= $p['title'] ?></td>
                        <td><?= $p['status'] ?></td>
                        <td>
                            <button class="secondary icon-edit edit-product-btn" 
                                    data-product-id="<?= $p['id'] ?>" 
                                    data-product-gid="<?= $p['id'] ?>">
                            </button>
                            <button class="secondary icon-trash"></button>
                        </td>
                    </tr>
                <?php
                    }
                ?>
            </tbody>
        </table>
    </section>

    <div class="pagination">
        <span class="button-group">
            
            <!-- Next Button -->
            <?php if ($pageInfo['hasNextPage']) : ?>
                <a href="<?php echo $baseUrl . '?shop=' . urlencode($shop) . '&host=' . urlencode($host) . '&title=' . urlencode($title) . '&status=' . urlencode($status) . '&limit=' . $limit . '&after=' . urlencode($pageInfo['endCursor']); ?>">
                <button class="secondary icon-next"></button>
                </a>
            <?php endif; ?>
        
        
            <!-- Prev Button -->
            <?php if ($pageInfo['hasPreviousPage']): ?>
                <a href="<?php echo $baseUrl . '?shop=' . urlencode($shop) . '&host=' . urlencode($host) . '&title=' . urlencode($title) . '&status=' . urlencode($status) . '&limit=' . $limit . '&before=' . urlencode($pageInfo['startCursor']); ?>">
                <button class="secondary icon-prev"></button>
                </a>
            <?php endif; ?>
            
        </span>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <script>

        $(document).ready(function() {
            // Fetch and populate categories
            async function loadCategories() {
                try {
                    const response = await fetch('ajax/get-categories.php?shop_url='+"<?= $shopify->get_url() ?>"+'&shop_token='+"<?= $shopify->get_token() ?>");
                    const result = await response.json();
                    // console.log(result);
                    // return;
                    if (result.success) {
                        const select = document.getElementById('productCategory');
                        select.innerHTML = '<option value="">Select category</option>';
                        
                        result.categories.forEach(cat => {
                            const option = document.createElement('option');
                            option.value = cat.id;
                            option.textContent = cat.name;
                            select.appendChild(option);
                        });

                        const select2 = document.getElementById('productType');
                        // select2.innerHTML = '<option value="">Select type</option>';
                        
                        result.types.forEach(type => {
                            const option2 = document.createElement('option');
                            option2.value = type.id;
                            option2.textContent = type.name;
                            select2.appendChild(option2);
                        });

                        
                    }
                } catch (error) {
                    console.error('Failed to load categories:', error);
                }
            }

            // Call on page load
            loadCategories();

            // Fetch and populate publications
            async function loadPublications() {
                try {
                    const response = await fetch('ajax/get-publications.php?shop_url='+"<?= $shopify->get_url() ?>"+'&shop_token='+"<?= $shopify->get_token() ?>");
                    const result = await response.json();

                    if (result.success) {
                        const container = document.getElementById('publicationsContainer');
                        container.innerHTML = '';
                        
                        result.publications.forEach(pub => {
                            const div = document.createElement('div');
                            div.className = 'mb-2';
                            div.innerHTML = `
                                <label class="flex items-center">
                                    <input type="checkbox" name="publish_channels[]" value="${pub.id}">
                                    <span class="ml-2">${pub.name}</span>
                                </label>
                            `;
                            container.appendChild(div);
                        });
                    }
                } catch (error) {
                    console.error('Failed to load publications:', error);
                }
            }

            // Call on page load
            loadPublications();


            // Tags functionality
            const tagContainer = document.getElementById('tagContainer');
            const tagInput = document.getElementById('tagInput');
            const tags = [];

            tagInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const tagValue = tagInput.value.trim();
                    if (tagValue && !tags.includes(tagValue)) {
                        tags.push(tagValue);
                        addTagElement(tagValue);
                        tagInput.value = '';
                    }
                }
            });

            function addTagElement(tag) {
                const tagEl = document.createElement('div');
                tagEl.className = 'tag';
                tagEl.innerHTML = `${tag} <span class="tag-remove" data-tag="${tag}">×</span>`;
                tagContainer.insertBefore(tagEl, tagInput);
            }

            function removeTag(tag) {
                const index = tags.indexOf(tag);
                if (index > -1) tags.splice(index, 1);
                renderTags();
            }

            $(document).on( 'click', '.tag-remove', function() {
                const url = $(this).data('tag');
                removeTag(url);
            });

            function renderTags() {
                tagContainer.querySelectorAll('.tag').forEach(el => el.remove());
                tags.forEach(addTagElement);
            }

            // Image functionality
            const imageUploadArea = document.getElementById('imageUploadArea');
            const imageFileInput = document.getElementById('imageFileInput');
            const imageUrlInput = document.getElementById('imageUrlInput');
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            const images = [];

            imageUploadArea.addEventListener('click', () => imageFileInput.click());
            
            // Handle FILE UPLOAD
            imageFileInput.addEventListener('change', async (e) => {
                const files = Array.from(e.target.files);

                for (const file of files) {
                    const formData = new FormData();
                    formData.append('image', file);

                    try {
                        const response = await fetch('ajax/upload-image.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();

                        if (result.success && result.url) {
                            addImage(result.url); // Add ngrok URL
                            $('#imageFileInput').val('');
                        }
                    } catch (error) {
                        console.error('Upload error:', error);
                        alert('Failed to upload image');
                    }
                }
            });

            imageUrlInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const url = imageUrlInput.value.trim();
                    if (url) {
                        addImage(url);
                        imageUrlInput.value = '';
                    }
                }
            });

            function addImage(url) {
                if (!images.includes(url)) {
                    images.push(url);
                    renderImages();
                    console.log(images);
                }
            }

            function removeImage(url) {
                const index = images.indexOf(url);
                if (index > -1) images.splice(index, 1);
                renderImages();
            }

            function renderImages() {
                imagePreviewContainer.innerHTML = images.map((url, i) => `
                    <div class="image-preview">
                        <img src="${url}" alt="Product image ${i + 1}">
                        <button type="button" class="image-remove" data-url="${url}">×</button>
                    </div>
                `).join('');
            }

            $(document).on( 'click', '.image-remove', function() {
                const url = $(this).data('url');
                removeImage(url);
            });

            let optionCount = 1;
            const maxOptions = 3;
            
            // Toggle variant mode
            $('#hasVariants').change(function() {
                if ($(this).is(':checked')) {
                    $('#optionsSection').show();
                    $('#basePrice').attr('placeholder', 'Base price for all variants');
                    $('#baseSKU').attr('placeholder', 'Will auto-generate for variants');
                } else {
                    $('#optionsSection').hide();
                    $('#variantsListContainer').hide();
                    $('#basePrice').attr('placeholder', '');
                    $('#baseSKU').attr('placeholder', '');
                }
            });
            
            // Add option button
            $('#addOptionBtn').click(function() {
                if (optionCount >= maxOptions) {
                    alert('Maximum 3 options allowed');
                    return;
                }
                optionCount++;
                
                const optionHtml = `
                    <div class="form-group mb-4 option-group" data-option="${optionCount}">
                        <label class="form-label">Option ${optionCount} name</label>
                        <input type="text" class="option-name form-control mb-2" placeholder="e.g., Material">
                        <label class="form-label">Option values</label>
                        <input type="text" class="option-values form-control" placeholder="Separate with comma">
                        <button type="button" class="btn btn-sm mt-2 remove-option-btn" style="background: #dc3545; color: white;">Remove</button>
                    </div>
                `;
                $('#optionsContainer').append(optionHtml);
            });
            
            // Remove option
            $(document).on('click', '.remove-option-btn', function() {
                $(this).closest('.option-group').remove();
                optionCount--;
            });
            
            // Generate variants button
            $('#generateVariantsBtn').click(function() {
                generateVariants();
            });
            
            let options_new = [];
            function generateVariants() {
                const basePrice = $('#basePrice').val();
                const baseSKU = $('#baseSKU').val() || 'PROD';
                const baseInventory = $('#baseInventory').val() || '0';
                
                if (!basePrice) {
                    alert('Please enter base price first');
                    return;
                }
                const options = [];
                $('.option-group').each(function() {
                    const name = $(this).find('.option-name').val().trim();
                    const values = $(this).find('.option-values').val()
                        .split(',')
                        .map(v => v.trim())
                        .filter(v => v);
                    
                    if (name && values.length > 0) {
                        options.push({ name, values });
                    }
                });
                
                if (options.length === 0) {
                    alert('Please add at least one option');
                    return;
                }

                options_new = options;
                
                const variants = generateCombinations(options);
                renderVariantTable(variants, basePrice, baseSKU, baseInventory);
                $('#variantsListContainer').show();
            }
            
            function generateCombinations(options) {
                if (options.length === 0) return [];
                if (options.length === 1) {
                    return options[0].values.map(v => [v]);
                }
                
                const result = [];
                const rest = generateCombinations(options.slice(1));
                
                options[0].values.forEach(value => {
                    rest.forEach(combination => {
                        result.push([value, ...combination]);
                    });
                });
                
                return result;
            }
            
            function renderVariantTable(variants, basePrice, baseSKU, baseInventory) {
                const tbody = $('#variantsTableBody');
                tbody.empty();
                
                variants.forEach((variant, index) => {
                    const variantName = variant.join(' / ');
                    // Auto-generate SKU: BASE-OPTION1-OPTION2
                    const autoSKU = baseSKU + '-' + variant.map(v => v.replace(/\s+/g, '-').toUpperCase()).join('-');
                    
                    const row = `
                        <tr style="border-bottom: 1px solid #e1e3e5;" data-variant-index="${index}">
                            <td style="padding: 8px; font-weight: 500;">${variantName}</td>
                            <td style="padding: 8px;">
                                <input type="number" class="form-control variant-price" step="0.01" value="${basePrice}" data-variant="${variantName}" required>
                            </td>
                            <td style="padding: 8px;">
                                <input type="text" class="form-control variant-sku" value="${autoSKU}" data-variant="${variantName}">
                            </td>
                            <td style="padding: 8px;">
                                <input type="number" class="form-control variant-inventory" value="${baseInventory}" data-variant="${variantName}">
                            </td>
                            <td style="padding: 8px;">
                                <button type="button" class="btn btn-sm btn-secondary upload-variant-image" data-variant="${variantName}">Upload Images</button>
                                <div class="variant-images-preview" data-variant="${variantName}" style="display: flex; gap: 4px; margin-top: 4px;"></div>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            }
            
            // Upload variant images
            $(document).on('click', '.upload-variant-image', function() {
                const variantName = $(this).data('variant');
                const input = $('<input type="file" accept="image/*" multiple style="display:none">');
                
                input.change(async function() {
                    const files = Array.from(this.files);
                    const previewContainer = $(`.variant-images-preview[data-variant="${variantName}"]`);
                    let variant_image_hidden = $(`.variant-images-preview[data-variant="${variantName}"] .variant-image-url[data-variant="${variantName}"]`);

                    for (const file of files) {
                        const formData = new FormData();
                        formData.append('image', file);
                        
                        try {
                            const response = await fetch('ajax/upload-image.php', {
                                method: 'POST',
                                body: formData
                            });
                            
                            const result = await response.json();
                            if (result.success && result.url) {
                                // Store URL and show preview
                                const imgPreview = `
                                    <div class="variant-img-thumb" style="width: 40px; height: 40px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; position: relative;">
                                        <img src="${result.url}" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                `;
                                previewContainer.append(imgPreview);

                                if(variant_image_hidden.length > 0){
                                    let variant_image_hidden_val = $(variant_image_hidden).val();
                                    variant_image_hidden_val += `,${result.url}`;
                                    $(variant_image_hidden).val(variant_image_hidden_val);
                                }else{
                                    variant_image_hidden = `<input type="hidden" class="variant-image-url" data-variant="${variantName}" value="${result.url}">`;
                                    previewContainer.append(variant_image_hidden);
                                }
                                
                            }
                        } catch (error) {
                            console.error('Upload error:', error);
                        }
                    }
                });
                
                input.click();
            });



            // Reset when opening for create
            $('#openModalBtn').on('click', function() {
                $('#modal_status').val("create");
                isEditMode = false;
                editProductId = null;
                $('#productForm')[0].reset();
                tags.length = 0;
                images.length = 0;
                $('#tagContainer .tag').remove();
                $('#imagePreviewContainer').empty();
                $('#variantsListContainer').hide();
                $('#optionsSection').hide();
                $('.modal-header').html('Create New Product <span class="close-btn remove-variant-btn" style="text-align: center;">&times;</span>');
                $('.submit-btn').html('Create Product');
                $('#customModal').fadeIn();
            });

            // Close modal using event delegation
            $(document).on('click', '.close-btn', function () {
                $('#modal_status').val("");
                $('#customModal').fadeOut();
            });

            async function productCreate(){
                // Show loader
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('Creating... <span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);
                
                // // Collect variants data
                // const variantRows = document.querySelectorAll('.variant-row');
                // const variants = Array.from(variantRows).map(row => ({
                //     title: row.querySelector('.variant-title').value || 'Default Title',
                //     price: row.querySelector('.variant-price').value,
                //     sku: row.querySelector('.variant-sku').value,
                //     inventory_quantity: parseInt(row.querySelector('.variant-inventory').value) || 0
                // }));


                let variants = [];
    
                if ($('#hasVariants').is(':checked')) {
                    // Collect variant data
                    $('#variantsTableBody tr').each(function() {
                        const variantName = $(this).find('td:first').text();
                        const options = variantName.split(' / ');
                        const imageUrlInput = $(this).find('.variant-image-url').val() || '';
                        const imageUrls = imageUrlInput ? imageUrlInput.split(',') : [];
                        variants.push({
                            title: variantName,
                            price: $(this).find('.variant-price').val() || '0',
                            sku: $(this).find('.variant-sku').val() || '',
                            inventory_quantity: parseInt($(this).find('.variant-inventory').val()) || 0,
                            image_url: imageUrls,
                            option1: options[0] || null,
                            option2: options[1] || null,
                            option3: options[2] || null
                        });
                    });
                } else {
                    // Simple product
                    variants = [{
                        title: document.getElementById('productTitle').value,
                        price: $('#basePrice').val(),
                        sku: $('#baseSKU').val() || '',
                        inventory_quantity: parseInt($('#baseInventory').val()) || 0,
                        option1: 'Default Title'
                    }];
                }


                // Collect publishing data
                const publishChannels = Array.from(document.querySelectorAll('input[name="publish_channels[]"]:checked'))
                    .map(cb => cb.value);

                const productData = {
                    title: document.getElementById('productTitle').value,
                    description: document.getElementById('productDescription').value,
                    vendor: document.getElementById('productVendor').value,
                    product_type: document.getElementById('productType').value,
                    category: document.getElementById('productCategory').value || null,
                    tags: tags,
                    images: images,
                    options: options_new,
                    variants: variants,
                    status: document.getElementById('productStatus').value,
                    publish_channels: publishChannels,
                    shop_url : "<?= $shopify->get_url() ?>",
                    shop_token : "<?= $shopify->get_token() ?>"
                };

                console.log('Product Data:', productData);
                
                // Send to your backend
                try {
                    const response = await fetch('ajax/createProduct.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(productData)
                    });
                    
                    const result = await response.json();
                    console.log('Result:', result);
                    alert('Product created successfully!');
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error creating product');
                } finally {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            }



            // Form submission
            document.getElementById('productForm').addEventListener('submit', async (e) => {
                e.preventDefault();

                let modal_status =  $('#modal_status').val();

                if(modal_status === "create"){
                    productCreate();
                }else if(modal_status === "edit"){
                    productEdit();
                }

            });

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////

            async function productEdit(){

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('Processing... <span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);
                
                // Collect data (same as before)
                let variants = [];
                
                if ($('#hasVariants').is(':checked')) {
                    $('#variantsTableBody tr').each(function() {
                        const variantId = $(this).data('variant-id') || null; // Get existing variant ID
                        const variantName = $(this).find('td:first').text();
                        const options = variantName.split(' / ');
                        const imageUrlInput = $(this).find('.variant-image-url').val() || '';
                        const imageUrls = imageUrlInput ? imageUrlInput.split(',') : [];
                        
                        variants.push({
                            id: variantId, // Include for updates
                            title: variantName,
                            price: $(this).find('.variant-price').val() || '0',
                            sku: $(this).find('.variant-sku').val() || '',
                            inventory_quantity: parseInt($(this).find('.variant-inventory').val()) || 0,
                            image_url: imageUrls,
                            option1: options[0] || null,
                            option2: options[1] || null,
                            option3: options[2] || null
                        });
                    });
                } else {
                    const variantId = isEditMode && product.variants ? product.variants[0].id : null;
                    variants = [{
                        id: variantId,
                        title: 'Default Title',
                        price: $('#basePrice').val(),
                        sku: $('#baseSKU').val() || '',
                        inventory_quantity: parseInt($('#baseInventory').val()) || 0,
                        option1: 'Default Title'
                    }];
                }
                
                const publishChannels = Array.from(document.querySelectorAll('input[name="publish_channels[]"]:checked'))
                    .map(cb => cb.value);
                
                const productData = {
                    product_id: editProductId, // Add product ID for edit mode
                    is_edit: isEditMode,
                    title: document.getElementById('productTitle').value,
                    description: document.getElementById('productDescription').value,
                    vendor: document.getElementById('productVendor').value,
                    product_type: document.getElementById('productType').value,
                    category: document.getElementById('productCategory').value || null,
                    tags: tags,
                    images: images,
                    options: options_new,
                    variants: variants,
                    status: document.getElementById('productStatus').value,
                    publish_channels: publishChannels,
                    shop_url: "<?= $shopify->get_url() ?>",
                    shop_token: "<?= $shopify->get_token() ?>"
                };
                
                console.log('Product Data:', productData);
                
                try {
                    const endpoint = isEditMode ? 'ajax/updateProduct.php' : 'ajax/createProduct.php';
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(productData)
                    });
                    
                    const result = await response.json();
                    console.log('Result:', result);
                    return;
                    if (result.success) {
                        alert(isEditMode ? 'Product updated successfully!' : 'Product created successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (result.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error processing product');
                } finally {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            }

            let isEditMode = false;
            let editProductId = null;

            // Edit product click
            $(document).on('click', '.edit-product-btn', async function() {
                $('#modal_status').val("edit");
                const productGid = $(this).data('product-gid');
                isEditMode = true;
                editProductId = productGid;
                
                // Show loading
                $('#customModal').fadeIn();
                $('.modal-header').html('Loading... <span class="close-btn remove-variant-btn" style="text-align: center;">&times;</span>');
                
                try {
                    const response = await fetch('ajax/get-product.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            product_id: productGid,
                            shop_url: "<?= $shopify->get_url() ?>",
                            shop_token: "<?= $shopify->get_token() ?>"
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        console.log(result.product);
                        populateProductForm(result.product);
                        $('.modal-header').html('Edit Product <span class="close-btn remove-variant-btn" style="text-align: center;">&times;</span>');
                    } else {
                        alert('Failed to load product');
                        $('#customModal').fadeOut();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error loading product');
                    $('#customModal').fadeOut();
                }
            });

            async function populateProductForm(product) {
                // Reset form first
                $('#productForm')[0].reset();
                tags.length = 0;
                images.length = 0;
                $('#tagContainer .tag').remove();
                $('#imagePreviewContainer').empty();
                $('#variantsListContainer').hide();
                $('#optionsSection').hide();
                $('#hasVariants').prop('checked', false);
                
                // Basic info
                $('#productTitle').val(product.title);
                $('#productDescription').val(product.description);
                $('#productVendor').val(product.vendor);
                $('#productType').val(product.product_type);
                $('#productStatus').val(product.status);
                
                // Tags
                product.tags.forEach(tag => {
                    tags.push(tag);
                    addTagElement(tag);
                });
                
                // Images
                product.images.forEach(url => {
                    images.push(url);
                });
                renderImages();
                
                // Check if simple or variant product
                if (product.is_simple) {
                    // Simple product
                    const variant = product.variants[0];
                    $('#basePrice').val(variant.price);
                    $('#baseSKU').val(variant.sku);
                    $('#baseInventory').val(variant.inventory_quantity);
                } else {
                    // Variant product
                    $('#hasVariants').prop('checked', true);
                    $('#optionsSection').show();
                    
                    // Populate options
                    $('#optionsContainer').empty();
                    optionCount = 0;
                    
                    product.options.forEach((option, index) => {
                        if (option.name === 'Title') return; // Skip default title
                        
                        optionCount++;
                        const optionHtml = `
                            <div class="form-group mb-4 option-group" data-option="${optionCount}">
                                <label class="form-label">Option ${optionCount} name</label>
                                <input type="text" class="option-name form-control mb-2" value="${option.name}">
                                <label class="form-label">Option values</label>
                                <input type="text" class="option-values form-control" value="${option.values.join(', ')}">
                                ${optionCount > 1 ? '<button type="button" class="btn btn-sm mt-2 remove-option-btn" style="background: #dc3545; color: white;">Remove</button>' : ''}
                            </div>
                        `;
                        $('#optionsContainer').append(optionHtml);
                    });
                    
                    // Set base values from first variant
                    const firstVariant = product.variants[0];
                    $('#basePrice').val(firstVariant.price);
                    $('#baseSKU').val(firstVariant.sku.split('-')[0]); // Extract base SKU
                    $('#baseInventory').val(firstVariant.inventory_quantity);
                    
                    // Generate and populate variants table
                    options_new = product.options.filter(opt => opt.name !== 'Title');
                    generateVariantsForEdit(product.variants);
                }

                // Fetch current publications for this product
                try {
                    const pubResponse = await fetch('ajax/get-product-publications.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            product_id: product.id,
                            shop_url: "<?= $shopify->get_url() ?>",
                            shop_token: "<?= $shopify->get_token() ?>"
                        })
                    });
                    
                    const pubResult = await pubResponse.json();
                    if (pubResult.success) {
                        pubResult.publication_ids.forEach(pubId => {
                            $(`input[name="publish_channels[]"][value="${pubId}"]`).prop('checked', true);
                        });
                    }
                } catch (error) {
                    console.error('Failed to load product publications:', error);
                }
                
                
                // Update submit button
                $('.submit-btn').html('Update Product');
            }

            function generateVariantsForEdit(existingVariants) {
                const tbody = $('#variantsTableBody');
                tbody.empty();
                
                existingVariants.forEach((variant, index) => {
                    if (variant.title === 'Default Title') return;
                    
                    const row = `
                        <tr style="border-bottom: 1px solid #e1e3e5;" data-variant-index="${index}" data-variant-id="${variant.id}">
                            <td style="padding: 8px; font-weight: 500;">${variant.title}</td>
                            <td style="padding: 8px;">
                                <input type="number" class="form-control variant-price" step="0.01" value="${variant.price}" required>
                            </td>
                            <td style="padding: 8px;">
                                <input type="text" class="form-control variant-sku" value="${variant.sku}">
                            </td>
                            <td style="padding: 8px;">
                                <input type="number" class="form-control variant-inventory" value="${variant.inventory_quantity}">
                            </td>
                            <td style="padding: 8px;">
                                <button type="button" class="btn btn-sm btn-secondary upload-variant-image" data-variant="${variant.title}">Upload Images</button>
                                <div class="variant-images-preview" data-variant="${variant.title}" style="display: flex; gap: 4px; margin-top: 4px;">
                                    ${variant.image_url.map(url => `
                                        <div class="variant-img-thumb" style="width: 40px; height: 40px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                                            <img src="${url}" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    `).join('')}
                                    <input type="hidden" class="variant-image-url" data-variant="${variant.title}" value="${variant.image_url.join(',')}">
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
                
                $('#variantsListContainer').show();
            }

        });
    </script>

    <!-- <script>
        function submitProductForm() {
            const title = document.getElementById('productTitle').value.trim();
            const description = document.getElementById('productDescription').value.trim();
            const price = document.getElementById('productPrice').value.trim();
            const sku = document.getElementById('productSKU').value.trim();

            if (!title || !price) {
                alert('Title and Price are required!');
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                if (res.success) {
                    alert('Product created! ID: ' + res.productId);
                    // Close modal
                    // parent.shopify.modal.hide('my-modal');
                    // document.getElementById('productForm').reset();
                } else {
                    alert('Error: ' + res.error);
                }
                } else {
                alert('AJAX error: ' + xhr.status);
                }
            };
            xhr.send(`title=${encodeURIComponent(title)}&description=${encodeURIComponent(description)}&price=${encodeURIComponent(price)}&sku=${encodeURIComponent(sku)}`);
        }

    </script> -->


<?php
    include_once("includes/footer.php");

    /*
    <!-- <ui-modal id="my-modal">
    <ui-title-bar title="Create a Product">
    <button variant="primary" onclick="submitProductForm()">Create</button>
    <button onclick="document.getElementById('my-modal').hide()">Cancel</button>
    </ui-title-bar>

    <form id="productForm" style="padding: 20px;">
    <label>
        Product Title:<br>
        <input type="text" id="productTitle" required>
    </label>
    <br><br>
    <label>
        Product Description:<br>
        <textarea id="productDescription" rows="3"></textarea>
    </label>
    <br><br>
    <label>
        Price:<br>
        <input type="number" id="productPrice" step="0.01" required>
    </label>
    <br><br>
    <label>
        SKU:<br>
        <input type="text" id="productSKU">
    </label>
    </form>
    </ui-modal>
    <button onclick="shopify.modal.show('my-modal')">Create Product</button> -->
    
    
    
    
    <!-- <form id="productForm" style="padding: 10px;">
    <label>
        Product Title:<br>
        <input type="text" id="productTitle" required>
    </label>
    <br><br>
    <label>
        Product Description:<br>
        <textarea id="productDescription" rows="3"></textarea>
    </label>
    <br><br>
    <label>
        Price:<br>
        <input type="number" id="productPrice" step="0.01" required>
    </label>
    <br><br>
    <label>
        SKU:<br>
        <input type="text" id="productSKU">
    </label>
    <br><br>
    <button type="submit" id="submitBtn">Submit</button>
    </form> -->
    
    


    // Variants functionality
    // const variantsContainer = document.getElementById('variantsContainer');
    // const addVariantBtn = document.getElementById('addVariantBtn');
    // addVariantBtn.addEventListener('click', () => {
    //     const variantRow = document.createElement('div');
    //     variantRow.className = 'variant-row';
    //     variantRow.innerHTML = `
    //         <div class="form-group">
    //             <label>Variant Title</label>
    //             <input type="text" class="variant-title" placeholder="e.g., Medium / Blue">
    //         </div>
    //         <div class="form-group">
    //             <label>Price *</label>
    //             <input type="number" class="variant-price" step="0.01" required>
    //         </div>
    //         <div class="form-group">
    //             <label>SKU</label>
    //             <input type="text" class="variant-sku">
    //         </div>
    //         <div class="form-group">
    //             <label>Inventory</label>
    //             <input type="number" class="variant-inventory" value="0">
    //         </div>
    //         <button type="button" class="remove-variant-btn" onclick="this.parentElement.remove()">X</button>
    //     `;
    //     variantsContainer.appendChild(variantRow);
    // });


    // Handle form submission
    // $('#productForm').on('submit', function(e) {
    //     e.preventDefault();

    //     const title = $('#productTitle').val().trim();
    //     const description = $('#productDescription').val().trim();
    //     const price = $('#productPrice').val().trim();
    //     const sku = $('#productSKU').val().trim();
    //     const shop_url = "<?= $shopify->get_url() ?>";
    //     const shop_token = "<?= $shopify->get_token() ?>";

    //     if (!title || !price) {
    //         alert('Title and Price are required!');
    //         return;
    //     }

    //     // console.log('Form Data:', { title, description, price, sku });

    //     // Example AJAX call to ajax.php
    //     $.ajax({
    //     url: 'ajax/createProduct.php',
    //     type: 'POST',
    //     data: { title, description, price, sku, shop_url, shop_token },
    //     success: function(response) {
    //         console.log('Server Response:', response);
    //         alert('Product created successfully!');
    //         console.log(response);
    //         // $('#customModal').fadeOut();
    //         // $('#productForm')[0].reset();
    //     },
    //     error: function(err) {
    //         console.error('Error:', err);
    //         alert('Something went wrong.');
    //     }
    //     });
    // });



    
    */
?>