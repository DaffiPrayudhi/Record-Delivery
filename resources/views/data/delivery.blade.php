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
        let errorSoundLoop;
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
                        playNotificationSound('success');
                        stopErrorSound(); 
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
                    playNotificationSound('error');
                });
            } else {
                handleErrorPopup('Format data QR Code tidak valid.');
                inputElement.value = "";
            }
        }

    function handleErrorPopup(message) {
            stopErrorSound();
            if (!isErrorSoundPlaying) {
                playNotificationSound('error'); 
                isErrorSoundPlaying = true; 
            }

            if (message === 'Format data salah!') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Format data salah!',
                    confirmButtonText: 'OK',
                    showConfirmButton: true
                }).then(() => {
                    stopErrorSound(); 
                    isErrorSoundPlaying = false; 
                });
            } else if (message === 'Tidak dapat menginput data melebihi quantity.') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message,
                    confirmButtonText: 'OK',
                    showConfirmButton: true
                }).then(() => {
                    stopErrorSound(); 
                    isErrorSoundPlaying = false;
                });
            } else if (message === 'Quantity tidak dapat melebihi quantity record') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message,
                    confirmButtonText: 'OK',
                    showConfirmButton: true
                }).then(() => {
                    stopErrorSound(); 
                    isErrorSoundPlaying = false;
                });
            } else if (message === 'Part Number tidak sesuai') {
                localStorage.setItem('showPasswordError', 'true');
                showPasswordProtectedPopup('Part Number tidak sesuai');
            } else if (message === 'Data sudah ada dalam database') {
                localStorage.setItem('showPasswordError', 'true');
                showPasswordProtectedPopup('Data sudah ada dalam database');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message,
                    confirmButtonText: 'OK',
                    showConfirmButton: true
                }).then(() => {
                    stopErrorSound(); 
                    isErrorSoundPlaying = false;
                });
            }
        }

        if (localStorage.getItem('showPasswordError')) {
            showPasswordProtectedPopup("Masukkan Password terlebih dahulu");
            if (!isErrorSoundPlaying) {  
                playNotificationSound('error');
                isErrorSoundPlaying = true; 
            }
        }

        function showPasswordProtectedPopup(errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                html: `<p>${errorMessage}</p>
                    <input type="password" id="passwordInput" class="swal2-input" placeholder="Masukkan password untuk menutup notifikasi">`,
                confirmButtonText: 'Submit',
                preConfirm: () => {
                    const password = document.getElementById('passwordInput').value;
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
                            localStorage.removeItem('showPasswordError'); 
                            stopErrorSound(); 
                            isErrorSoundPlaying = false; 
                            return true;
                        } else {
                            throw new Error(data.message || 'Password salah.');
                        }
                    })
                    .catch(error => {
                        Swal.showValidationMessage(error.message);
                        if (!isErrorSoundPlaying) {
                            playNotificationSound('error');
                            isErrorSoundPlaying = true;
                        }
                    });
                }
            }).then(result => {
                if (!result.isConfirmed) {
                    localStorage.setItem('showPasswordError', 'true');
                    showPasswordProtectedPopup(errorMessage);
                } else {
                    document.getElementById('passwordInput').focus();
                }
            }).catch(error => {
                Swal.showValidationMessage(error.message);
            });
        }

        function stopErrorSound() {
            if (errorSoundLoop) {
                errorSoundLoop = false; 
            }
            isErrorSoundPlaying = false; 
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

        function playNotificationSound(type) {
        const context = new (window.AudioContext || window.webkitAudioContext)();
        let oscillator = context.createOscillator();
        let gainNode = context.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(context.destination);

        if (type === 'success') {
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(780, context.currentTime);
            oscillator.frequency.exponentialRampToValueAtTime(1760, context.currentTime + 0.3);
        } else if (type === 'error') {
            oscillator.type = 'triangle';
            oscillator.frequency.setValueAtTime(220, context.currentTime);
            oscillator.frequency.exponentialRampToValueAtTime(110, context.currentTime + 0.3);
        }

        gainNode.gain.setValueAtTime(0, context.currentTime);
        gainNode.gain.linearRampToValueAtTime(1, context.currentTime + 0.1);
        gainNode.gain.exponentialRampToValueAtTime(0.001, context.currentTime + 0.5);

        oscillator.start();
        
        errorSoundLoop = true;
        setTimeout(() => {
            if (errorSoundLoop) {
                playNotificationSound('error'); 
            }
        }, 1000);

        setTimeout(() => oscillator.stop(), 1000);
    }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/howler"></script>

<script>
    function resetForm() {
        var form = document.getElementById('dataForm');
        form.reset();
        document.getElementById('qrcode').focus();
        document.getElementById('record-table').style.display = 'none';
        document.getElementById('delivery-table').style.display = 'none';
    }

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
                playNotificationSound('success');
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    showConfirmButton: true,
                    timer: 5000 
                });
                playNotificationSound('error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Gagal membandingkan data.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            playNotificationSound('error');
        });
    }

    function playNotificationSound(type) {
        const context = new (window.AudioContext || window.webkitAudioContext)();
        let oscillator = context.createOscillator();
        let gainNode = context.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(context.destination);

        if (type === 'success') {
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(780, context.currentTime);
            oscillator.frequency.exponentialRampToValueAtTime(1760, context.currentTime + 0.3);
        } else if (type === 'error') {
            oscillator.type = 'triangle';
            oscillator.frequency.setValueAtTime(220, context.currentTime);
            oscillator.frequency.exponentialRampToValueAtTime(110, context.currentTime + 0.3);
        } else if (type === 'warning') {
            oscillator.type = 'square';
            oscillator.frequency.setValueAtTime(440, context.currentTime);
            setTimeout(() => oscillator.frequency.setValueAtTime(660, context.currentTime + 0.2), 200);
        }

        gainNode.gain.setValueAtTime(0, context.currentTime);
        gainNode.gain.linearRampToValueAtTime(1, context.currentTime + 0.1);
        gainNode.gain.exponentialRampToValueAtTime(0.001, context.currentTime + 0.5);

        oscillator.start();
        setTimeout(() => oscillator.stop(), 2000); 
    }
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
