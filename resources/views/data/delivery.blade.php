@extends('layouts.app')

@section('header')
    <h2 class="text-3xl font-semibold text-gray-800">
        {{ __('Form Input') }}
    </h2>
@endsection

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <h1>Scan Record Delivery - {{ session('no_transaksi') }}</h1>
        <div class="table-responsive">
            <table class="table">
                <thead class="table-header">
                    <tr>
                        <th>Tanggal</th>
                        <th>Model</th>
                        <th>Part Number</th> 
                        <th>Plant Destination</th>
                        <th>Tipe Delivery</th> 
                        <th>PIC</th>
                        <th>Quantity Record</th>
                        <th>Quantity Delivery</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ session('tgl_bln_thn') }}</td>
                        <td>{{ session('model') }}</td>
                        <td>
                            @if(session('part_numbers') && is_array(session('part_numbers')))
                                @foreach(session('part_numbers') as $partNumber)
                                    <p>{{ $partNumber }}</p> 
                                @endforeach
                            @else
                                <p></p>
                            @endif
                        </td>
                        <td>{{ session('plant_dest') }}</td>
                        <td>{{ session('tipe_delv') }}</td>
                        <td>{{ session('pic') }}</td>
                        <td>{{ $totalQtyValueRcrd }}</td> 
                        <td class="{{ $totalQtyValue != $totalQtyValueRcrd ? 'text-danger' : 'text-success' }}">
                            {{ $totalQtyValue }}
                        </td>
                        <td colspan="8"></td>                     
                    </tr>
                </tbody>
            </table>
        </div>  
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form id="dataForm" autocomplete="off">
                            @csrf
                            <input type="hidden" name="no_transaksi" value="{{ $noTransaksi }}">
                            <div class="mb-3">
                                <label for="qrcode" class="form-label">Scan Data:</label>
                                <input type="text" class="form-control @error('qrcode') is-invalid @enderror" id="qrcode" name="qrcode" required autofocus>
                                @error('qrcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="notification" style="display: none;" class="alert"></div>
                            <div class="form-actions">
                                <button type="button" onclick="resetForm()" class="btn btn-outline-danger">Reset</button>
                                <button type="button" class="btn btn-success" onclick="compareQty()">Finish</button>
                            </div>
                        </form>
                        <div id="record-table" style="display: none; margin-top: 10px">
                            <h3>Record Data</h3>
                            <table class="table table-hover">
                                <thead class="table-header">
                                    <tr>
                                        <th>No Transaksi</th>
                                        <th>Model</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="record-data"></tbody>
                            </table>
                        </div>

                        <div id="delivery-table" style="display: none;">
                            <h3>Delivery Data</h3>
                            <table class="table table-hover">
                                <thead class="table-header">
                                    <tr>
                                        <th>No Transaksi</th>
                                        <th>Max Model</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="delivery-data"></tbody>
                            </table>
                        </div>
                        <div id="toast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 10px; right: 10px; display: none;">
                            <div class="d-flex">
                                <div class="toast-body" id="toastMessage"></div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                        <h3 style="margin-top: 10px">Data Input</h3>
                        <div class="table-responsive">
                            <table id="deliveryTable" class="table table-hover">
                                <thead class="table-header">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Part Number</th>
                                        <th>Lot Number</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- javascript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </div>
</div>

<script>
$(document).ready(function() {
    var table = $('#deliveryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('delivery.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'tgl_bln_thn', name: 'tgl_bln_thn' },
            { data: 'part_number', name: 'part_number' },
            { data: 'lot_number', name: 'lot_number' },
            { data: 'qty', name: 'qty' }
        ],
        order: [[1, 'desc']],
        stateSave: true,
        paging: true,
        searching: false,
        ordering: true,
        info: false,
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
        lengthChange: false
    });
});
</script>

<script>
    $(document).ready(function() {
        const table = $('#deliveryTable').DataTable();
        const formElement = document.getElementById('dataForm');
        const inputElement = document.getElementById('qrcode');
        const notificationElement = document.getElementById('notification');
        let isErrorSoundPlaying = false;
        
        formElement.addEventListener('submit', function(e) {
            e.preventDefault();
            const qrData = inputElement.value.trim();
            processQRData(qrData);
        });

        function processQRData(qrData) {
            const dataArray = qrData.split('|');

            if (dataArray.length >= 4) {
                const formData = new FormData(document.getElementById('dataForm'));
                formData.append('tgl_bln_thn', new Date().toISOString().slice(0, 19).replace('T', ' '));
                formData.append('part_number', dataArray[0]);
                formData.append('qty', dataArray[2]);
                formData.append('lot_number', dataArray[3]);
                formData.append('flag', 1);

                fetch("{{ route('delivery.store') }}", {
                    method: "POST",
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayNotification('Data berhasil disimpan!', 'success');
                        playSuccessSound(); 
                        inputElement.value = ""; 
                        table.ajax.reload();
                        updateQuantityDelivery();
                    } else {
                        handleErrorPopup(data.message);
                        inputElement.value = "";
                    }
                })
                .catch(error => {
                    displayNotification('Terjadi kesalahan: ' + error.message, 'danger');
                    playErrorSound();
                });
            } else {
                handleErrorPopup('Format data QR Code tidak valid.');
                inputElement.value = "";
            }
        }

        function handleErrorPopup(message) {
            stopErrorSound();
            if (!isErrorSoundPlaying) {
                playErrorSound();
                isErrorSoundPlaying = true;
            }

            if (['Format data salah!', 'Tidak dapat menginput data melebihi quantity', 'Quantity tidak dapat melebihi quantity record'].includes(message)) { 
                localStorage.setItem('showPasswordError', 'true');
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: `<p>${message}</p>
                        <textarea id="noteInput" class="swal2-input" placeholder="Masukkan catatan" style="height: 100px; width: 100%; resize: none; padding: 10px; font-size: 1rem; border-radius: 10px; border: 1px solid #dcdcdc;"></textarea>`,
                    confirmButtonText: 'OK',
                    preConfirm: () => {
                        const note = document.getElementById('noteInput').value.trim();
                        if (!note) {
                            Swal.showValidationMessage('Catatan tidak boleh kosong.');
                            return false;
                        }
                        return note;
                    },
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        const note = result.value;
                        const noTransaksi = "{{ session('no_transaksi') }}";
                        const tglBlnThn = new Date().toISOString().slice(0, 19).replace('T', ' ');

                        fetch("{{ route('save.log') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            },
                            body: JSON.stringify({
                                no_transaksi: noTransaksi,
                                tgl_bln_thn: tglBlnThn,
                                note: note
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                stopErrorSound();
                                isErrorSoundPlaying = false;
                                localStorage.removeItem('showPasswordError');
                            } else {
                                Swal.showValidationMessage('Gagal menyimpan log.');
                            }
                        })
                        .catch(error => {
                            Swal.showValidationMessage(error.message);
                        });
                    }
                });
            } else if (['Part Number tidak sesuai', 'Data sudah ada dalam database'].includes(message)) {
                localStorage.setItem('showPasswordError', 'true');
                showPasswordProtectedPopup(message);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: `<p>${message}</p>
                        <textarea id="noteInput" class="swal2-input" placeholder="Masukkan catatan" style="height: 100px; width: 100%; resize: none; padding: 10px; font-size: 1rem; border-radius: 10px; border: 1px solid #dcdcdc;"></textarea>`,
                    confirmButtonText: 'OK',
                    preConfirm: () => {
                        const note = document.getElementById('noteInput').value.trim();
                        if (!note) {
                            Swal.showValidationMessage('Catatan tidak boleh kosong.');
                            return false;
                        }
                        return true;
                    },
                    allowOutsideClick: false
                });
            }
        }

        if (localStorage.getItem('showPasswordError')) {
            showPasswordProtectedPopup("Masukkan Password terlebih dahulu");
            if (!isErrorSoundPlaying) {  
                playErrorSound();
                isErrorSoundPlaying = true; 
            }
        }

        function showPasswordProtectedPopup(errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                html: `<p>${errorMessage}</p>
                    <textarea id="noteInput" class="swal2-input" placeholder="Masukkan catatan" autocomplete="off" style="height: 100px; width: 100%; resize: none; padding: 10px; font-size: 1rem; border-radius: 10px; border: 1px solid #dcdcdc;"></textarea>
                    <input type="password" id="passwordInput" class="swal2-input" placeholder="Masukkan password" style="padding: 10px; font-size: 1rem; border-radius: 10px; border: 1px solid #dcdcdc;">`,  
                confirmButtonText: 'Submit',
                preConfirm: () => {
                    const password = document.getElementById('passwordInput').value;
                    const note = document.getElementById('noteInput').value;
                    if (!note.trim()) {
                        Swal.showValidationMessage('Catatan tidak boleh kosong.');
                        return false; 
                    }
                    return fetch("{{ route('verify.password') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        },
                        body: JSON.stringify({ password })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const noTransaksi = "{{ session('no_transaksi') }}";
                            const tglBlnThn = new Date().toISOString().slice(0, 19).replace('T', ' '); 

                            return fetch("{{ route('save.log') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                },
                                body: JSON.stringify({
                                    no_transaksi: noTransaksi,
                                    tgl_bln_thn: tglBlnThn,
                                    note: note
                                })
                            });
                        }
                        throw new Error('Password salah.');
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            stopErrorSound();
                            isErrorSoundPlaying = false;
                            localStorage.removeItem('showPasswordError');
                        } else {
                            Swal.showValidationMessage('Gagal menyimpan log.');
                        }
                    })
                    .catch(error => {
                        Swal.showValidationMessage(error.message);
                    });
                },
                allowOutsideClick: false
            });
        }

        function updateQuantityDelivery() {
            const noTransaksi = "{{ session('no_transaksi') }}";
            
            fetch(`/delivery/${noTransaksi}/total-qty`)
                .then(response => response.json())
                .then(data => {
                    const totalQty = data.total_qty;
                    const qtyColumn = document.querySelector('td.text-danger, td.text-success');
                    if (qtyColumn) {
                        qtyColumn.textContent = totalQty; 
                        qtyColumn.className = totalQty !== "{{ $totalQtyValueRcrd }}" ? 'text-danger' : 'text-success'; 
                    }
                })
                .catch(error => console.error('Error fetching total quantity:', error));
        }

        function displayNotification(message, type) {
            notificationElement.textContent = message;
            notificationElement.className = `alert alert-${type}`;
            notificationElement.style.display = 'block';

            setTimeout(() => {
                notificationElement.style.display = 'none';
            }, 10000);
        }

        function playSuccessSound() {
            const sound = new Howl({
                src: ['/B/assets/audio/sukses.wav'],
                volume: 1,
                loop: false,
                onend: function() {
                    isErrorSoundPlaying = false;
                }
            });
            sound.play();
        }

        function playErrorSound() {
            const sound = new Howl({
                src: ['/B/assets/audio/error.wav'],
                volume: 1,
                loop: true,
                onend: function() {
                    isErrorSoundPlaying = false;
                }
            });
            sound.play();
            isErrorSoundPlaying = true;
        }

        function stopErrorSound() {
            isErrorSoundPlaying = false; 
            Howler.stop(); 
        }
    });
</script>

<script>
    function resetForm() {
        var form = document.getElementById('dataForm');
        form.reset();
        document.getElementById('qrcode').focus();
        document.getElementById('record-table').style.display = 'none';
        document.getElementById('delivery-table').style.display = 'none';
    }

    let errorSoundLoop = null;

    function compareQty() {
        fetch("{{ route('delivery.compare') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({}) 
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    showConfirmButton: true
                }).then(() => {
                    window.location.href = "{{ route('record.create') }}"; 
                });
                playSuccessSound();
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    showConfirmButton: true
                }).then(() => {
                    stopErrorSound();
                });
                playErrorSound();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Gagal membandingkan data.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                stopErrorSound();
            });
            playErrorSound();
        });
    }

    function playSuccessSound() {
        const sound = new Howl({
            src: ['/B/assets/audio/sukses.wav'],
            volume: 1,
            loop: false,
            onend: function() {
                isErrorSoundPlaying = false;
            }
        });
        sound.play();
    }

    function playErrorSound() {
        if (errorSoundLoop) {
            stopErrorSound();
        }
        errorSoundLoop = new Howl({
            src: ['/B/assets/audio/error.wav'],
            volume: 1,
            loop: true,
            onend: function() {
                isErrorSoundPlaying = false;
            }
        });
        errorSoundLoop.play();
        isErrorSoundPlaying = true;
    }

    function stopErrorSound() {
        if (errorSoundLoop) {
            errorSoundLoop.stop();
            errorSoundLoop = null; 
        }
    }

    document.addEventListener('click', function(event) {
        const swalPopup = document.querySelector('.swal2-container');
        if (!swalPopup.contains(event.target)) {
            stopErrorSound();  
        }
    });
</script>

<!-- <script>
    let typingTimer;
    const inputElement = document.getElementById('qrcode');
    const notificationElement = document.getElementById('notification');

    inputElement.addEventListener('input', function () {
        clearTimeout(typingTimer); 
        typingTimer = setTimeout(() => {
            const qrData = inputElement.value;
            const dataArray = qrData.split('|');

            if (dataArray.length >= 4) {
                const formData = new FormData(document.getElementById('dataForm'));
                formData.append('tgl_bln_thn', new Date().toISOString().slice(0, 19).replace('T', ' '));
                formData.append('part_number', dataArray[0]);
                formData.append('qty', dataArray[2]);
                formData.append('lot_number', dataArray[3]);
                formData.append('flag', 1);

                fetch("{{ route('delivery.store') }}", {
                    method: "POST",
                    body: formData,
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayNotification('Data berhasil disimpan!', 'success');
                            inputElement.value = ""; 
                        } else {
                            displayNotification('Gagal menyimpan data: ' + data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        displayNotification('Terjadi kesalahan: ' + error.message, 'danger');
                    });
            } else {
                displayNotification('Format data QR Code tidak valid.', 'warning');
            }
        }, 3000); 
    });

    function displayNotification(message, type) {
        notificationElement.textContent = message;
        notificationElement.className = `alert alert-${type}`; 
        notificationElement.style.display = 'block'; 

        setTimeout(() => {
            notificationElement.style.display = 'none';
        }, 10000);
    }
</script> -->

<style>
    .form-actions {
        display: flex;
        justify-content: space-between; 
        margin-top: 20px; 
    }

    .table-responsive {
        overflow-x: hidden;
    }

    .table-header th {
        color: #fff; 
        background-color: #2088ef;
    }

    .text-danger {
        background-color: lightcoral !important;
        color: black !important;
        font-weight: bold;
    }

    .text-success {
        background-color: lightgreen !important;
        color: black !important;
        font-weight: bold;
    }

</style>


@endsection
