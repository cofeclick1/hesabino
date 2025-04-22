$(document).ready(function() {
    // نمایش مودال افزودن پروژه
    $('#btnNewProject').on('click', function() {
        $('#projectForm')[0].reset();
        $('#projectForm').removeClass('was-validated');
        $('#projectModal').modal('show');
    });

    // ذخیره پروژه جدید
    $('#saveProject').on('click', function() {
        const form = $('#projectForm')[0];
        if (!form.checkValidity()) {
            $(form).addClass('was-validated');
            return;
        }

        const formData = new FormData(form);
        
        Swal.fire({
            title: 'در حال ذخیره...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: BASE_PATH + '/api/save-project.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    const project = response.project;
                    const option = new Option(project.name, project.id, true, true);
                    $(option).data('logo', project.logo_path);
                    $('select[name="project_id"]').append(option).trigger('change');
                    
                    $('#projectModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'موفقیت',
                        text: response.message,
                        confirmButtonText: 'تایید'
                    });
                    
                    form.reset();
                    $(form).removeClass('was-validated');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: xhr.responseJSON?.message || 'خطا در ارتباط با سرور',
                    confirmButtonText: 'تایید'
                });
            }
        });
    });
});