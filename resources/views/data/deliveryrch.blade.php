@extends('layouts.app')

@section('header')
    <h2 class="text-3xl font-semibold text-gray-800">
        {{ __('Form Input') }}
    </h2>
@endsection

@section('content')
<div class="page-content">
    <div class="container-fluid">
    <h1>Scan Paco Delivery - {{session('no_transaksi')}}</h1>
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
                            </div>
                        </form>
                        <div id="toast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 10px; right: 10px; display: none;">
                            <div class="d-flex">
                                <div class="toast-body" id="toastMessage"></div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="table-header">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Model</th>
                                                    <th>Part Number</th> 
                                                    <th>Quantity Receh</th>
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
                                                    <td>{{ session('qty_receh') }}</td> 
                                                    <td colspan="8"></td>                     
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>  
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </div>
</div>

<script>
    $(document).ready(function() {
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

                fetch("{{ route('deliveryrch.store') }}", {
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
                        window.location.href = "{{ route('delivery.createreceh') }}";
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
            dd(dataArray);
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
            } else if (message === 'Part Number tidak sesuai') {
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

        function stopErrorSound() {
            if (errorSoundLoop) {
                errorSoundLoop = false; 
            }
            isErrorSoundPlaying = false; 
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

<script>
    function resetForm() {
        var form = document.getElementById('dataForm');
        form.reset();
        document.getElementById('qrcode').focus();
        document.getElementById('record-table').style.display = 'none';
        document.getElementById('delivery-table').style.display = 'none';
    }
</script>

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
