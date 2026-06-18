@if (session('success') || session('error') || session('status') || session('saved') || $errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if (session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: @json(session('success')),
        timer: 3500,
        timerProgressBar: true,
        showConfirmButton: false
    });
    @endif
    @if (session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: @json(session('error')),
        confirmButtonText: 'OK'
    });
    @endif
    @if (session('status'))
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: @json(session('status')),
        timer: 3500,
        timerProgressBar: true,
        showConfirmButton: false
    });
    @endif
    @if (session('saved'))
    Swal.fire({
        icon: 'success',
        title: 'Saved',
        text: 'Changes saved successfully.',
        timer: 2500,
        timerProgressBar: true,
        showConfirmButton: false
    });
    @endif
    @if ($errors->any())
    Swal.fire({
        icon: 'error',
        title: 'Please fix the following errors:',
        html: '<ul style="text-align:left;">' + @json($errors->all()).map(e => '<li>' + e + '</li>').join('') + '</ul>',
        confirmButtonText: 'OK',
        width: '40em'
    });
    @endif
});
</script>
@endif
