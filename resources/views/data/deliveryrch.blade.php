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
    let errorSoundInstance;
    let isErrorSoundPlaying = false;

    function stopErrorSound() {
        if (errorSoundInstance) {
            errorSoundInstance.stop();
            isErrorSoundPlaying = false;
        }
    }

    function playNotificationSound(type) {
        const successSound = '{{ asset('B/assets/audio/sukses.wav') }}'; 
        const errorSound = '{{ asset('B/assets/audio/error.wav') }}';  

        if (type === 'success') {
            const successSoundInstance = new Howl({
                src: [successSound],
                loop: false, 
                volume: 1.0,
                onend: function() {
                    console.log('Success sound finished');
                }
            });
            successSoundInstance.play();
        } else if (type === 'error') {
            if (errorSoundInstance) {
                errorSoundInstance.stop();
            }

            errorSoundInstance = new Howl({
                src: [errorSound],
                loop: true, 
                volume: 1.0,
                onend: function() {
                    console.log('Error sound finished');
                }
            });

            errorSoundInstance.play();
            isErrorSoundPlaying = true;
        }
    }

    $(document).ready(function() {
        const formElement = document.getElementById('dataForm');
        const inputElement = document.getElementById('qrcode');
        const notificationElement = document.getElementById('notification');

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
        }

        function handleErrorPopup(message) {
            stopErrorSound();
            if (!isErrorSoundPlaying) {
                playNotificationSound('error');
                isErrorSoundPlaying = true;
            }

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                html: `<p>${message}</p>
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
                    return fetch("{{ route('verify.passwordrch') }}", {
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
                            const now = new Date();
                            const tglBlnThn = new Intl.DateTimeFormat('en-GB', {
                                timeZone: 'Asia/Jakarta',
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                            }).format(now).replace(/\//g, '-').replace(',', '');


                            return fetch("{{ route('save.logrch') }}", {
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
                                    localStorage.removeItem('showPasswordError'); 
                                    stopErrorSound(); 
                                    return true;
                                } else {
                                    throw new Error('Gagal menyimpan log.');
                                }
                            })
                            .catch(error => {
                                Swal.showValidationMessage(error.message);
                                if (!isErrorSoundPlaying) {
                                    playNotificationSound('error');
                                    isErrorSoundPlaying = true;
                                }
                            });
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
                },
                allowOutsideClick: false
            }).then(result => {
                if (!result.isConfirmed) {
                    localStorage.setItem('showPasswordError', 'true');
                    showPasswordProtectedPopup(errorMessage);
                } else {
                    document.getElementById('passwordInput').focus();
                }
            }).catch(error => {
                Swal.showValidationMessage(error.message);
            }).finally(() => {
                isErrorSoundPlaying = false;  
            });

            localStorage.setItem('showPasswordError', 'true');
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
                    return fetch("{{ route('verify.passwordrch') }}", {
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
                            const now = new Date();
                            const tglBlnThn = new Intl.DateTimeFormat('en-GB', {
                                timeZone: 'Asia/Jakarta',
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                            }).format(now).replace(/\//g, '-').replace(',', '');


                            return fetch("{{ route('save.logrch') }}", {
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
                                    localStorage.removeItem('showPasswordError'); 
                                    stopErrorSound(); 
                                    return true;
                                } else {
                                    throw new Error('Gagal menyimpan log.');
                                }
                            })
                            .catch(error => {
                                Swal.showValidationMessage(error.message);
                                if (!isErrorSoundPlaying) {
                                    playNotificationSound('error');
                                    isErrorSoundPlaying = true;
                                }
                            });
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
            }).finally(() => {
                isErrorSoundPlaying = false;  
            });
        }

        function displayNotification(message, type) {
            notificationElement.textContent = message;
            notificationElement.className = `alert alert-${type}`;
            notificationElement.style.display = 'block';

            setTimeout(() => {
                notificationElement.style.display = 'none';
            }, 10000);
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
