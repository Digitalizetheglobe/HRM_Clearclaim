{{ Form::open(['route' => ['employee.import'], 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'importEmployeeForm', 'class' => 'needs-validation', 'novalidate' => true]) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12 mb-4 text-center">
            <i class="ti ti-file-spreadsheet text-primary" style="font-size: 3rem;"></i>
            <h5 class="mt-2">{{ __('Import Employee Data') }}</h5>
            <p class="text-muted">{{ __('Please upload a CSV file containing employee records.') }}</p>
        </div>

        <div class="col-md-12 mb-4 d-flex justify-content-between align-items-center bg-light p-3 rounded">
            <div>
                <h6 class="mb-0">{{ __('Need a template?') }}</h6>
                <small class="text-muted">{{ __('Download the sample CSV file to see the required format.') }}</small>
            </div>
            <a href="{{ asset(Storage::url('uploads/sample')) . '/sample-employee2.csv' }}"
                class="btn btn-sm btn-outline-primary">
                <i class="ti ti-download me-1"></i> {{ __('Download Sample') }}
            </a>
        </div>

        <div class="col-md-12">
            <div class="form-group mb-0">
                <label for="file" class="form-label fw-bold">{{ __('Upload File') }} <span class="text-danger">*</span></label>
                <div class="choose-files position-relative">
                    <label for="file" class="w-100 p-4 border border-2 border-dashed rounded text-center cursor-pointer hover-bg-light transition-all" id="upload-area">
                        <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex p-3 mb-2">
                            <i class="ti ti-upload fs-3"></i>
                        </div>
                        <h6 class="mb-1">{{ __('Click to choose file') }} <span class="text-muted fw-normal">{{ __('or drag and drop') }}</span></h6>
                        <small class="text-muted">CSV files only (Max: 5MB)</small>
                        <input type="file" class="form-control d-none" name="file" id="file" data-filename="file" accept=".csv" required>
                    </label>
                    <div class="invalid-feedback mt-2" id="fileError">
                        {{ __('Please select a valid CSV file to upload.') }}
                    </div>
                </div>
                <div id="file-preview" class="d-none mt-3 p-3 bg-light rounded d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-file-text fs-3 text-primary me-2"></i>
                        <div>
                            <h6 class="mb-0 text-dark" id="fileName"></h6>
                            <small class="text-muted" id="fileSize"></small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeFile">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer border-top-0 pt-0">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary" id="uploadBtn">
        <i class="ti ti-check me-1"></i> {{ __('Upload & Import') }}
    </button>
</div>
{{ Form::close() }}

<style>
    .border-dashed { border-style: dashed !important; }
    .cursor-pointer { cursor: pointer; }
    .hover-bg-light:hover { background-color: #f8f9fa; }
    .transition-all { transition: all 0.3s ease; }
    .bg-primary-subtle { background-color: rgba(var(--bs-primary-rgb), 0.1); }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('file');
        const fileNameSpan = document.getElementById('fileName');
        const fileSizeSpan = document.getElementById('fileSize');
        const filePreview = document.getElementById('file-preview');
        const uploadArea = document.getElementById('upload-area');
        const removeFileBtn = document.getElementById('removeFile');
        const form = document.getElementById('importEmployeeForm');
        const fileError = document.getElementById('fileError');

        // Drag and drop events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            uploadArea.classList.add('bg-light', 'border-primary');
        }

        function unhighlight(e) {
            uploadArea.classList.remove('bg-light', 'border-primary');
        }

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFiles(files[0]);
            }
        }

        // File input change event
        fileInput.addEventListener('change', function(event) {
            if (this.files.length > 0) {
                handleFiles(this.files[0]);
            }
        });

        function handleFiles(file) {
            // Validate extension
            const ext = file.name.split('.').pop().toLowerCase();
            if (ext !== 'csv') {
                showError('Only CSV files are allowed.');
                resetFile();
                return;
            }

            // Validate size (e.g., 5MB)
            if (file.size > 5 * 1024 * 1024) {
                showError('File size must be less than 5MB.');
                resetFile();
                return;
            }

            // Hide error and show preview
            fileInput.classList.remove('is-invalid');
            fileError.style.display = 'none';
            uploadArea.style.display = 'none';
            filePreview.classList.remove('d-none');
            
            fileNameSpan.innerText = file.name;
            
            // Format size
            let size = (file.size / 1024).toFixed(2);
            let unit = 'KB';
            if (size > 1024) {
                size = (size / 1024).toFixed(2);
                unit = 'MB';
            }
            fileSizeSpan.innerText = size + ' ' + unit;
        }

        removeFileBtn.addEventListener('click', resetFile);

        function resetFile() {
            fileInput.value = '';
            uploadArea.style.display = 'block';
            filePreview.classList.add('d-none');
        }

        function showError(message) {
            fileInput.classList.add('is-invalid');
            fileError.innerText = message;
            fileError.style.display = 'block';
        }

        // Form validation
        form.addEventListener('submit', function(event) {
            if (!fileInput.value) {
                event.preventDefault();
                event.stopPropagation();
                showError('Please select a CSV file to upload.');
            } else if (fileInput.classList.contains('is-invalid')) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // Show loading state on button
                const btn = document.getElementById('uploadBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Uploading...';
            }
        });
    });
</script>
