{{-- Enhanced Property Image Upload Component --}}
@props([
    'property' => null,
    'maxFiles' => 20,
    'maxFileSize' => 8192, // 8MB in KB
    'allowedTypes' => ['jpeg', 'jpg', 'png', 'webp']
])

<div class="enhanced-image-upload" data-max-files="{{ $maxFiles }}" data-max-size="{{ $maxFileSize }}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <div class="col-lg-9 col-xl-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h2 mb-1">Property Images</h1>
                <p class="text-muted small mb-0">
                    Maximum {{ $maxFiles }} images, {{ $maxFileSize/1024 }}MB each. 
                    Formats: {{ implode(', ', $allowedTypes) }}
                </p>
            </div>
            <div class="image-counter">
                <span class="badge bg-secondary" id="image-count">
                    {{ $property?->images->count() ?? 0 }}/{{ $maxFiles }}
                </span>
            </div>
        </div>

        <!-- Upload Progress -->
        <div id="upload-progress" class="mb-3 d-none">
            <div class="progress mb-2" style="height: 6px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: 0%"></div>
            </div>
            <small class="text-muted">Uploading images... <span id="progress-text">0%</span></small>
        </div>

        <!-- Alert Messages -->
        <div id="upload-alerts"></div>

        <!-- Drop Zone -->
        <div class="upload-drop-zone border-2 border-dashed rounded-4 p-4 mb-4 text-center" id="drop-zone">
            <div class="upload-content">
                <i class="fi-cloud-upload fs-1 text-primary mb-3"></i>
                <h4>Drag & Drop Images Here</h4>
                <p class="text-muted mb-3">or click to browse files</p>
                <button type="button" class="btn btn-primary" id="browse-button">
                    <i class="fi-plus me-2"></i>Choose Images
                </button>
            </div>
            <input type="file" id="file-input" name="images[]" multiple accept="image/*" class="d-none">
        </div>

        <!-- Images Preview Grid -->
        <div class="images-preview" id="images-preview">
            <div class="row g-3" id="images-grid">
                
                <!-- Existing Images -->
                @if($property && $property->images->count() > 0)
                    @foreach($property->images as $index => $image)
                        <div class="col-6 col-md-4 col-lg-3" data-image-id="{{ $image->id }}">
                            <div class="image-preview-item">
                                <div class="position-relative overflow-hidden rounded">
                                    <!-- Primary Badge -->
                                    @if($image->is_primary)
                                        <span class="badge bg-success position-absolute top-0 start-0 z-3 m-2">
                                            <i class="fi-star me-1"></i>Primary
                                        </span>
                                    @endif
                                    
                                    <!-- Image -->
                                    <div class="ratio ratio-4x3">
                                        <img src="{{ Storage::url($image->image_path) }}" 
                                             alt="{{ $image->alt_text }}" 
                                             class="object-fit-cover">
                                    </div>
                                    
                                    <!-- Overlay Controls -->
                                    <div class="image-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center opacity-0">
                                        <div class="btn-group">
                                            @if(!$image->is_primary)
                                                <button type="button" class="btn btn-sm btn-light set-primary" 
                                                        data-image-id="{{ $image->id }}" title="Set as Primary">
                                                    <i class="fi-star"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-light edit-image" 
                                                    data-image-id="{{ $image->id }}" title="Edit Alt Text">
                                                <i class="fi-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-image" 
                                                    data-image-id="{{ $image->id }}" title="Delete Image">
                                                <i class="fi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Image Info -->
                                <div class="mt-2">
                                    <small class="text-muted d-block">{{ $image->alt_text ?: 'No description' }}</small>
                                    <small class="text-muted">{{ $loop->iteration }}/{{ $property->images->count() }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
                
            </div>
        </div>

        <!-- Video Tour Section -->
        <div class="video-tour-section mt-4 pt-4 border-top">
            <h3 class="h4 mb-3">Video Tour (Optional)</h3>
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="video_tour_url" class="form-label">YouTube or Vimeo URL</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fi-video"></i>
                        </span>
                        <input type="url" class="form-control" id="video_tour_url" name="video_tour_url" 
                               value="{{ old('video_tour_url', $property->video_tour_url ?? '') }}" 
                               placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <small class="form-text text-muted">
                        Paste a link to your property video tour from YouTube or Vimeo
                    </small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Preview</label>
                    <div id="video-preview" class="border rounded p-3 text-center text-muted">
                        <i class="fi-video fs-2 mb-2"></i>
                        <div>No video URL provided</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Edit Modal -->
<div class="modal fade" id="imageEditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Image Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="image-edit-form">
                    <input type="hidden" id="edit-image-id">
                    <div class="mb-3">
                        <label for="image-alt-text" class="form-label">Description/Alt Text</label>
                        <input type="text" class="form-control" id="image-alt-text" 
                               placeholder="Describe this image...">
                        <small class="form-text text-muted">
                            Help visitors understand what's in this image
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-image-details">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<style>
.enhanced-image-upload .upload-drop-zone {
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.enhanced-image-upload .upload-drop-zone:hover,
.enhanced-image-upload .upload-drop-zone.drag-over {
    border-color: var(--bs-primary);
    background-color: var(--bs-primary-bg-subtle);
}

.enhanced-image-upload .image-preview-item {
    transition: transform 0.3s ease;
}

.enhanced-image-upload .image-preview-item:hover {
    transform: translateY(-2px);
}

.enhanced-image-upload .image-overlay {
    background: rgba(0, 0, 0, 0.7);
    transition: opacity 0.3s ease;
}

.enhanced-image-upload .image-preview-item:hover .image-overlay {
    opacity: 1;
}

.enhanced-image-upload .sortable-ghost {
    opacity: 0.5;
}

.enhanced-image-upload .progress {
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadComponent = new EnhancedImageUpload({
        dropZone: '#drop-zone',
        fileInput: '#file-input',
        browseButton: '#browse-button',
        imagesGrid: '#images-grid',
        maxFiles: {{ $maxFiles }},
        maxFileSize: {{ $maxFileSize }},
        allowedTypes: {!! json_encode($allowedTypes) !!},
        propertyId: {{ $property->id ?? 'null' }},
        uploadUrl: '{{ route("properties.images.upload", $property ?? 0) }}',
        deleteUrl: '{{ route("properties.images.delete", ["property" => $property ?? 0, "image" => ":id"]) }}',
        setPrimaryUrl: '{{ route("properties.images.set-primary", ["property" => $property ?? 0, "image" => ":id"]) }}'
    });
});

class EnhancedImageUpload {
    constructor(options) {
        this.options = options;
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupDragAndDrop();
        this.setupSortable();
        this.updateImageCounter();
    }
    
    setupEventListeners() {
        // Browse button click
        document.querySelector(this.options.browseButton).addEventListener('click', () => {
            document.querySelector(this.options.fileInput).click();
        });
        
        // File input change
        document.querySelector(this.options.fileInput).addEventListener('change', (e) => {
            this.handleFiles(e.target.files);
        });
        
        // Delete image buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.delete-image')) {
                const imageId = e.target.closest('.delete-image').dataset.imageId;
                this.deleteImage(imageId);
            }
            
            if (e.target.closest('.set-primary')) {
                const imageId = e.target.closest('.set-primary').dataset.imageId;
                this.setPrimaryImage(imageId);
            }
            
            if (e.target.closest('.edit-image')) {
                const imageId = e.target.closest('.edit-image').dataset.imageId;
                this.editImage(imageId);
            }
        });
        
        // Video URL preview
        const videoInput = document.getElementById('video_tour_url');
        if (videoInput) {
            videoInput.addEventListener('input', (e) => {
                this.updateVideoPreview(e.target.value);
            });
        }
    }
    
    setupDragAndDrop() {
        const dropZone = document.querySelector(this.options.dropZone);
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, this.preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
        });
        
        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            this.handleFiles(files);
        }, false);
    }
    
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    handleFiles(files) {
        const fileArray = Array.from(files);
        const validFiles = this.validateFiles(fileArray);
        
        if (validFiles.length > 0) {
            this.uploadFiles(validFiles);
        }
    }
    
    validateFiles(files) {
        const currentCount = document.querySelectorAll('[data-image-id]').length;
        const validFiles = [];
        
        files.forEach(file => {
            if (currentCount + validFiles.length >= this.options.maxFiles) {
                this.showAlert('danger', `Maximum ${this.options.maxFiles} images allowed.`);
                return;
            }
            
            if (file.size > this.options.maxFileSize * 1024) {
                this.showAlert('danger', `${file.name} is too large. Maximum size is ${this.options.maxFileSize/1024}MB.`);
                return;
            }
            
            const extension = file.name.split('.').pop().toLowerCase();
            if (!this.options.allowedTypes.includes(extension)) {
                this.showAlert('danger', `${file.name} has invalid format. Allowed: ${this.options.allowedTypes.join(', ')}`);
                return;
            }
            
            validFiles.push(file);
        });
        
        return validFiles;
    }
    
    uploadFiles(files) {
        const formData = new FormData();
        files.forEach(file => formData.append('images[]', file));
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        
        this.showProgress(0);
        
        fetch(this.options.uploadUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            this.hideProgress();
            if (data.success) {
                this.showAlert('success', 'Images uploaded successfully!');
                location.reload(); // Refresh to show new images
            } else {
                this.showAlert('danger', data.message || 'Upload failed');
            }
        })
        .catch(error => {
            this.hideProgress();
            this.showAlert('danger', 'Upload failed: ' + error.message);
        });
    }
    
    showProgress(percent) {
        const progressContainer = document.getElementById('upload-progress');
        const progressBar = progressContainer.querySelector('.progress-bar');
        const progressText = document.getElementById('progress-text');
        
        progressContainer.classList.remove('d-none');
        progressBar.style.width = percent + '%';
        progressText.textContent = Math.round(percent) + '%';
    }
    
    hideProgress() {
        document.getElementById('upload-progress').classList.add('d-none');
    }
    
    showAlert(type, message) {
        const alertsContainer = document.getElementById('upload-alerts');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertsContainer.appendChild(alert);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    
    updateImageCounter() {
        const count = document.querySelectorAll('[data-image-id]').length;
        document.getElementById('image-count').textContent = `${count}/${this.options.maxFiles}`;
    }
    
    updateVideoPreview(url) {
        const preview = document.getElementById('video-preview');
        if (!url) {
            preview.innerHTML = `
                <i class="fi-video fs-2 mb-2"></i>
                <div>No video URL provided</div>
            `;
            return;
        }
        
        let embedUrl = '';
        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            const videoId = this.extractYouTubeId(url);
            embedUrl = `https://www.youtube.com/embed/${videoId}`;
        } else if (url.includes('vimeo.com')) {
            const videoId = this.extractVimeoId(url);
            embedUrl = `https://player.vimeo.com/video/${videoId}`;
        }
        
        if (embedUrl) {
            preview.innerHTML = `
                <iframe src="${embedUrl}" width="100%" height="100" frameborder="0" allowfullscreen></iframe>
            `;
        } else {
            preview.innerHTML = `
                <i class="fi-video fs-2 mb-2 text-warning"></i>
                <div>Invalid video URL</div>
            `;
        }
    }
    
    extractYouTubeId(url) {
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }
    
    extractVimeoId(url) {
        const regExp = /vimeo.com\/(\d+)/;
        const match = url.match(regExp);
        return match ? match[1] : null;
    }
}
</script>