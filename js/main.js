// File Upload — show selected filename

const resumeFile = document.getElementById('resumeFile');
const fileSelected = document.getElementById('fileSelected');
const fileName = document.getElementById('fileName');

if (resumeFile) {
    resumeFile.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const file = this.files[0];

            // Validate file is PDF
            if (file.type !== 'application/pdf') {
                alert('Please upload a PDF file only!');
                this.value = ''; // clear the input
                return;
            }

            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB!');
                this.value = '';
                return;
            }

            // Show filename
            fileName.textContent = file.name;
            fileSelected.style.display = 'block';
        }
    });
}

// Show loading state on form submit
const uploadForm = document.getElementById('uploadForm');
const submitBtn = document.getElementById('submitBtn');

if (uploadForm) {
    uploadForm.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.textContent = '⏳ Analyzing your resume... please wait';
        // Button is disabled so user can't click twice
    });
}