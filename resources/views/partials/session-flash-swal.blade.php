@if (session('success') || session('error') || session('status') || session('saved') || $errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if (session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: @json(session('success')),
        confirmButtonText: 'OK',
        confirmButtonColor: '#65a30d',
        width: '36em'
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
        confirmButtonText: 'OK',
        confirmButtonColor: '#65a30d',
        width: '36em'
    });
    @endif
    @if (session('saved'))
    Swal.fire({
        icon: 'success',
        title: 'Saved',
        text: 'Changes saved successfully.',
        confirmButtonText: 'OK',
        confirmButtonColor: '#65a30d'
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
