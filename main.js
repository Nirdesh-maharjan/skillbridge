document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
        });
    }

    // Add smooth scrolling to anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Add fade-in animation to feature cards
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.feature-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Auto-scroll messages to bottom
    const messagesContainer = document.getElementById('messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Profile picture preview and validation
    const profilePicInput = document.querySelector('input[type="file"][name="profile_pic"]');
    if (profilePicInput) {
        const fileValidation = document.getElementById('fileValidation');
        const profilePicPreview = document.getElementById('profilePicPreview');
        
        profilePicInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            fileValidation.textContent = '';
            
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    fileValidation.textContent = 'Only JPG, PNG, and GIF images are allowed.';
                    return;
                }
                
                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    fileValidation.textContent = 'Image must be less than 2MB.';
                    return;
                }
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function(event) {
                    profilePicPreview.src = event.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Password strength indicator
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        const strengthBar = document.querySelector('.password-strength-bar');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Character type checks
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update strength bar
            let width = 0;
            let color = '#dc3545'; // red
            
            if (strength <= 2) {
                width = 33 * strength;
                color = '#dc3545'; // red
            } else if (strength <= 4) {
                width = 33 * strength;
                color = '#ffc107'; // yellow
            } else {
                width = 100;
                color = '#28a745'; // green
            }
            
            strengthBar.style.width = width + '%';
            strengthBar.style.background = color;
        });
    }
});

$('#filter-form').on('change', function() {
    var selectedCategory = $('#category').val();
    var budgetRange = $('#budget').val();

    $.ajax({
        url: 'fetch_jobs.php',
        method: 'POST',
        data: { category: selectedCategory, budget: budgetRange },
        success: function(response) {
            $('#job-listings').html(response);
        }
    });
});

$('#apply-form').submit(function(e) {
    e.preventDefault();

    $.ajax({
        url: 'apply_job.php',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            var res = JSON.parse(response);
            alert(res.message);
            if (res.status == 'success') {
                $('#apply-form')[0].reset();
            }
        }
    });
});

$('#profileForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'update-profile.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if(response.status === 'success'){
                alert(response.message);
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('An unexpected error occurred.');
        }
    });
});

function loadJobs() {
    $.ajax({
        url: 'fetch_jobs.php',
        method: 'GET',
        dataType: 'json',
        success: function(jobs) {
            let html = '';
            jobs.forEach(function(job){
                html += `
                    <div class="job-card">
                        <h3>${job.title}</h3>
                        <p>Category: ${job.category}</p>
                        <p>Budget: $${job.budget}</p>
                        <p>Posted: ${job.created_at}</p>
                        <a href="job-details.php?id=${job.job_id}" class="btn btn-primary">View Job</a>
                    </div>
                `;
            });
            $('#job-listings').html(html);
        }
    });
}
