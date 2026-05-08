@extends('layouts.appikom')

@section('title', '- Pendaftaran Kursus')

@section('content')
<link rel="stylesheet" href="{{ asset('css/stylecoursereg.css') }}">

<div class="coursereg-container">
    <div class="coursereg-card">
        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form class="coursereg-form" action="{{ route('coursereg.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="nama_kursus">Nama Kursus</label>
                <div class="select-wrapper">
                    <select id="nama_kursus" name="nama_kursus" required>
                        <option value="" disabled selected>Pilih Kursus</option>
                        <option value="Inovasi Digital">Inovasi Digital</option>
                        <option value="Komuniti Digital">Komuniti Digital</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="semester">Semester</label>
                <div class="select-wrapper">
                    <select id="semester" name="semester" required>
                        <option value="" disabled selected>Pilih Semester</option>
                        <option value="Semester 1">Semester 1</option>
                        <option value="Semester 2">Semester 2</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="tahun">Tahun Akademik</label>
                <div class="select-wrapper">
                    <select id="tahun" name="tahun" required>
                        <option value="" disabled selected>Pilih Tahun Akademik</option>
                        <option value="2025/2026">2025/2026</option>
                        <option value="2026/2027">2026/2027</option>
                        <option value="2027/2028">2027/2028</option>
                        <option value="2028/2029">2028/2029</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Daftar Kursus</button>
                <button type="button" class="btn-preview" onclick="openPreviewModal()">Senarai Kursus</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pratonton -->
<div class="modal-overlay" id="previewModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Senarai Kursus Berdaftar</h3>
            <button class="modal-close" onclick="closePreviewModal()">&times;</button>
        </div>
        <div class="modal-body">
            @if(isset($courses) && count($courses) > 0)
                <table class="preview-table">
                    <thead>
                        <tr>
                            <th>Nama Kursus</th>
                            <th>Semester</th>
                            <th>Tahun Akademik</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses as $c)
                            <tr>
                                <td>{{ $c->fld_krs_nama }}</td>
                                <td>{{ $c->fld_krs_semester }}</td>
                                <td>{{ $c->fld_krs_tahun }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="empty-courses">Tiada kursus didaftarkan lagi.</p>
            @endif
        </div>
    </div>
</div>

<script>
    function openPreviewModal() {
        const modal = document.getElementById('previewModal');
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }

    function closePreviewModal() {
        const modal = document.getElementById('previewModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
</script>
@endsection
