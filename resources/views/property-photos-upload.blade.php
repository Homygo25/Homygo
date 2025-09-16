{{-- Property Photos Upload Component --}}
<div class="property-photos-upload" data-property-id="{{ $property->id ?? '' }}">
    <!-- Meta Token for CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Photos and Videos Section -->
    <div class="col-lg-9 col-xl-8">
        <h1 class="h2 pb-1 pb-lg-2">Photos and videos</h1>
        <p class="fs-sm mb-0">The maximum photo size is 8 MB. Formats: jpeg, jpg, png. Put the main picture first.</p>
        <p class="fs-sm">Drag and drop or click to upload your property images.</p>
        
        <!-- Upload Status -->
        <div id="upload-status" class="alert alert-info d-none mb-3" role="alert">
            <div class="d-flex">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div>Uploading images...</div>
            </div>
        </div>
        
        <!-- Error Messages -->
        <div id="upload-errors" class="alert alert-danger d-none mb-3" role="alert"></div>
        
        <!-- Images Grid -->
        <div class="border rounded p-3">
            <div class="row row-cols-2 row-cols-sm-3 g-2" id="images-grid">
                
                <!-- Existing Images -->
                @if(isset($property) && $property->images->count() > 0)
                    @foreach($property->images as $image)
                        <div class="col image-item" data-image-id="{{ $image->id }}">
                            <div class="hover-effect-opacity position-relative overflow-hidden rounded">
                                @if($image->is_primary)
                                    <span class="badge text-bg-primary position-absolute top-0 start-0 z-3 mt-2 ms-2">Primary</span>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-primary position-absolute top-0 start-0 z-3 mt-2 ms-2 set-primary" 
                                            data-image-id="{{ $image->id }}" title="Set as primary">
                                        Set Primary
                                    </button>
                                @endif
                                
                                <div class="ratio" style="--fn-aspect-ratio: calc(180 / 268 * 100%)">
                                    <img src="{{ $image->url }}" alt="Property Image" class="object-fit-cover">
                                </div>
                                
                                <div class="hover-effect-target position-absolute top-0 start-0 d-flex align-items-center justify-content-center w-100 h-100 opacity-0">
                                    <button type="button" class="btn btn-icon btn-sm btn-danger delete-image me-2" 
                                            data-image-id="{{ $image->id }}" aria-label="Remove">
                                        <i class="fi-trash fs-base"></i>
                                    </button>
                                    @if(!$image->is_primary)
                                        <button type="button" class="btn btn-icon btn-sm btn-primary set-primary" 
                                                data-image-id="{{ $image->id }}" aria-label="Set as primary">
                                            <i class="fi-star fs-base"></i>
                                        </button>
                                    @endif
                                    <span class="position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 z-1"></span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                <!-- Upload Button -->
                <div class="col">
                    <div class="d-flex align-items-center justify-content-center position-relative h-100 cursor-pointer bg-body-tertiary border-2 border-dotted rounded p-3" 
                         onclick="document.getElementById('image-upload').click()">
                        <div class="text-center">
                            <i class="fi-plus-circle fs-4 text-secondary-emphasis mb-2"></i>
                            <div class="hover-effect-underline fs-sm fw-medium">Upload photos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hidden File Input -->
        <input type="file" id="image-upload" name="images[]" multiple accept="image/*" style="display: none;">
        
        <!-- Video Tour Link -->
        <div class="pt-3 mt-3">
            <label for="video_tour_url" class="form-label">Link to the video tour</label>
            <div class="position-relative">
                <i class="fi-link position-absolute top-50 start-0 translate-middle-y fs-lg ms-3"></i>
                <input type="url" class="form-control form-control-lg form-icon-start" 
                       id="video_tour_url" name="video_tour_url" 
                       value="{{ old('video_tour_url', $property->video_tour_url ?? '') }}" 
                       placeholder="www.youtube.com/...">
            </div>
        </div>
    </div>
</div>

<style>
.property-photos-upload .image-item {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

.property-photos-upload .upload-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background-color: rgba(0,0,0,0.1);
    border-radius: 0 0 0.375rem 0.375rem;
}

.property-photos-upload .upload-progress-bar {
    height: 100%;
    background-color: #0d6efd;
    border-radius: 0 0 0.375rem 0.375rem;
    transition: width 0.3s ease;
}

.property-photos-upload .drag-over {
    border-color: #0d6efd !important;
    background-color: rgba(13, 110, 253, 0.1) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const propertyId = document.querySelector('.property-photos-upload').dataset.propertyId;
    const fileInput = document.getElementById('image-upload');
    const imagesGrid = document.getElementById('images-grid');
    const uploadButton = imagesGrid.querySelector('.col:last-child');
    const uploadStatus = document.getElementById('upload-status');
    const uploadErrors = document.getElementById('upload-errors');
    
    // CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // File upload handling
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            uploadImages(e.target.files);
        }
    });
    
    // Drag and drop functionality
    const uploadArea = uploadButton;
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });
    
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, unhighlight, false);
    });
    
    uploadArea.addEventListener('drop', handleDrop, false);
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight(e) {
        uploadArea.classList.add('drag-over');
    }
    
    function unhighlight(e) {
        uploadArea.classList.remove('drag-over');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        uploadImages(files);
    }
    
    // Upload images function
    function uploadImages(files) {
        if (!propertyId) {
            showError('Property ID is required for image upload');
            return;
        }
        
        const formData = new FormData();
        
        Array.from(files).forEach((file, index) => {
            formData.append('images[]', file);
        });
        
        formData.append('_token', token);
        
        showStatus('Uploading images...');
        hideError();
        
        fetch(`/properties/${propertyId}/images`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => response.json())
        .then(data => {
            hideStatus();
            
            if (data.success) {
                // Add new images to grid
                data.images.forEach(image => {
                    addImageToGrid(image);
                });
                
                showSuccess(`Successfully uploaded ${data.images.length} image(s)`);
            } else {
                showError(data.message || 'Failed to upload images');
            }
        })
        .catch(error => {
            hideStatus();
            showError('An error occurred while uploading images');
            console.error('Upload error:', error);
        });
        
        // Reset file input
        fileInput.value = '';
    }
    
    // Add image to grid
    function addImageToGrid(image) {
        const imageCol = document.createElement('div');
        imageCol.className = 'col image-item';
        imageCol.dataset.imageId = image.id;
        
        imageCol.innerHTML = `
            <div class="hover-effect-opacity position-relative overflow-hidden rounded">
                ${image.is_primary ? 
                    '<span class="badge text-bg-primary position-absolute top-0 start-0 z-3 mt-2 ms-2">Primary</span>' : 
                    '<button type="button" class="btn btn-sm btn-outline-primary position-absolute top-0 start-0 z-3 mt-2 ms-2 set-primary" data-image-id="' + image.id + '" title="Set as primary">Set Primary</button>'
                }
                
                <div class="ratio" style="--fn-aspect-ratio: calc(180 / 268 * 100%)">
                    <img src="${image.url}" alt="Property Image" class="object-fit-cover">
                </div>
                
                <div class="hover-effect-target position-absolute top-0 start-0 d-flex align-items-center justify-content-center w-100 h-100 opacity-0">
                    <button type="button" class="btn btn-icon btn-sm btn-danger delete-image me-2" data-image-id="${image.id}" aria-label="Remove">
                        <i class="fi-trash fs-base"></i>
                    </button>
                    ${!image.is_primary ? 
                        '<button type="button" class="btn btn-icon btn-sm btn-primary set-primary" data-image-id="' + image.id + '" aria-label="Set as primary"><i class="fi-star fs-base"></i></button>' : 
                        ''
                    }
                    <span class="position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 z-1"></span>
                </div>
            </div>
        `;
        
        // Insert before upload button
        imagesGrid.insertBefore(imageCol, uploadButton);
        
        // Add event listeners
        attachImageEvents(imageCol);
    }
    
    // Attach events to image elements
    function attachImageEvents(imageElement) {
        const deleteBtn = imageElement.querySelector('.delete-image');
        const setPrimaryBtn = imageElement.querySelector('.set-primary');
        
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                deleteImage(this.dataset.imageId);
            });
        }
        
        if (setPrimaryBtn) {
            setPrimaryBtn.addEventListener('click', function() {
                setPrimaryImage(this.dataset.imageId);
            });
        }
    }
    
    // Delete image
    function deleteImage(imageId) {
        if (!confirm('Are you sure you want to delete this image?')) {
            return;
        }
        
        fetch(`/images/${imageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
                if (imageElement) {
                    imageElement.remove();
                }
                showSuccess('Image deleted successfully');
            } else {
                showError(data.message || 'Failed to delete image');
            }
        })
        .catch(error => {
            showError('An error occurred while deleting the image');
            console.error('Delete error:', error);
        });
    }
    
    // Set primary image
    function setPrimaryImage(imageId) {
        fetch(`/images/${imageId}/primary`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI to reflect new primary image
                updatePrimaryImageUI(imageId);
                showSuccess('Primary image updated successfully');
            } else {
                showError(data.message || 'Failed to set primary image');
            }
        })
        .catch(error => {
            showError('An error occurred while setting primary image');
            console.error('Set primary error:', error);
        });
    }
    
    // Update primary image UI
    function updatePrimaryImageUI(newPrimaryId) {
        // Remove primary badges and add set primary buttons to all images
        document.querySelectorAll('.image-item').forEach(item => {
            const imageId = item.dataset.imageId;
            const badge = item.querySelector('.badge');
            const setPrimaryBtn = item.querySelector('.set-primary');
            
            if (imageId === newPrimaryId) {
                // This is the new primary image
                if (badge) badge.remove();
                if (setPrimaryBtn) setPrimaryBtn.remove();
                
                const imageDiv = item.querySelector('.hover-effect-opacity');
                imageDiv.insertAdjacentHTML('afterbegin', 
                    '<span class="badge text-bg-primary position-absolute top-0 start-0 z-3 mt-2 ms-2">Primary</span>'
                );
            } else {
                // This is not primary anymore
                if (badge && badge.textContent === 'Primary') {
                    badge.remove();
                    const imageDiv = item.querySelector('.hover-effect-opacity');
                    imageDiv.insertAdjacentHTML('afterbegin', 
                        `<button type="button" class="btn btn-sm btn-outline-primary position-absolute top-0 start-0 z-3 mt-2 ms-2 set-primary" data-image-id="${imageId}" title="Set as primary">Set Primary</button>`
                    );
                    
                    // Re-attach event listener
                    const newSetPrimaryBtn = item.querySelector('.set-primary');
                    if (newSetPrimaryBtn) {
                        newSetPrimaryBtn.addEventListener('click', function() {
                            setPrimaryImage(this.dataset.imageId);
                        });
                    }
                }
            }
        });
    }
    
    // Status and error functions
    function showStatus(message) {
        uploadStatus.textContent = message;
        uploadStatus.classList.remove('d-none');
    }
    
    function hideStatus() {
        uploadStatus.classList.add('d-none');
    }
    
    function showError(message) {
        uploadErrors.textContent = message;
        uploadErrors.classList.remove('d-none');
    }
    
    function hideError() {
        uploadErrors.classList.add('d-none');
    }
    
    function showSuccess(message) {
        // You can implement a success notification here
        console.log('Success:', message);
    }
    
    // Attach events to existing images
    document.querySelectorAll('.image-item').forEach(attachImageEvents);
});
</script>
