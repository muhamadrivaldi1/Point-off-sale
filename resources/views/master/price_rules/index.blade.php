@extends('layouts.app')

@section('title','Master Harga Bertingkat')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-tags"></i> Master Harga Bertingkat
        </div>
        <div>
            <input type="text" id="searchInput" class="form-control form-control-sm"
                   placeholder="Cari produk..." style="width: 200px;">
        </div>
    </div>

    <div class="card-body">

        {{-- ALERT --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" id="successAlert">
                {{ session('success') }}
            </div>
        @endif

        <div class="accordion" id="productsAccordion">
            @foreach($units as $unit)
                @php
                    // Mapping value ke label bahasa Indonesia
                    $priceTypeLabels = [
                        'retail' => 'Retail / Eceran',
                        'wholesale' => 'Wholesale / Grosir',
                        'member' => 'Member / Anggota',
                    ];
                @endphp

                <div class="accordion-item mb-3 product-item">
                    <h2 class="accordion-header" id="heading{{ $unit->id }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse{{ $unit->id }}" aria-expanded="false"
                                aria-controls="collapse{{ $unit->id }}">
                            {{ $unit->product->name }} <span class="text-muted">({{ $unit->unit_name }})</span>
                        </button>
                    </h2>
                    <div id="collapse{{ $unit->id }}" class="accordion-collapse collapse"
                         aria-labelledby="heading{{ $unit->id }}" data-bs-parent="#productsAccordion">
                        <div class="accordion-body">

                            {{-- TABLE HARGA --}}
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="100">Min Qty</th>
                                        <th>Harga</th>
                                        <th>Type</th>
                                        <th width="150">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($unit->priceRules as $rule)
                                        <tr id="rule-row-{{ $rule->id }}">
                                            <td>{{ $rule->min_qty }}</td>
                                            <td>Rp {{ number_format($rule->price) }}</td>
                                            <td>{{ $priceTypeLabels[$rule->price_type] ?? $rule->price_type }}</td>
                                            <td class="text-center">
                                                {{-- EDIT --}}
                                                <button class="btn btn-sm btn-warning me-1" 
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editModal{{ $rule->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                {{-- DELETE --}}
                                                <form method="POST"
                                                      action="{{ route('price-rules.destroy', $rule->id) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Hapus harga ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        {{-- EDIT MODAL --}}
                                        <div class="modal fade" id="editModal{{ $rule->id }}" tabindex="-1"
                                             aria-labelledby="editModalLabel{{ $rule->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="{{ route('price-rules.update', $rule->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editModalLabel{{ $rule->id }}">
                                                                Edit Harga
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label>Min Qty</label>
                                                                <input type="number" name="min_qty" class="form-control"
                                                                       value="{{ $rule->min_qty }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Harga</label>
                                                                <input type="number" name="price" class="form-control"
                                                                       value="{{ $rule->price }}" required>
                                                            </div>
                                                           <div class="mb-3">
                                                                <label>Type</label>
                                                                <select name="price_type" class="form-control" required>
                                                                    @foreach($priceTypeLabels as $value => $label)
                                                                        <option value="{{ $value }}" @selected($rule->price_type == $value)>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                Batal
                                                            </button>
                                                            <button type="submit" class="btn btn-primary">
                                                                Simpan
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                Belum ada harga bertingkat
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            {{-- FORM TAMBAH HARGA --}}
                            <form method="POST" action="{{ route('price-rules.store') }}" class="row g-2 mt-3">
                                @csrf
                                <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                                <div class="col-md-3">
                                    <input type="number" name="min_qty" class="form-control" placeholder="Min Qty" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="price" class="form-control" placeholder="Harga" required>
                                </div>
                                <div class="col-md-3">
                                    <select name="price_type" class="form-control" required>
                                        @foreach($priceTypeLabels as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary w-100">
                                        <i class="bi bi-plus-circle"></i> Tambah
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    // SEARCH FILTER
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(item => {
            const name = item.querySelector('.accordion-button').innerText.toLowerCase();
            item.style.display = name.includes(filter) ? '' : 'none';
        });
    });

    // AUTO HIDE ALERT
    const alertBox = document.getElementById('successAlert');
    if(alertBox){
        setTimeout(() => {
            alertBox.classList.add('fade');
            alertBox.classList.add('d-none');
        }, 3000);
    }
</script>
@endpush
@endsection
