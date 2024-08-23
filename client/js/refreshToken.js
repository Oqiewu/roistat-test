// refreshToken.js

document.addEventListener('DOMContentLoaded', function() {
    const refreshTokenButton = document.getElementById('refreshTokenButton');
    const refreshStatus = document.getElementById('refreshStatus');

    function checkAndUpdateToken() {
        fetch('http://localhost:8000/callback.php?action=check_token')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    refreshStatus.textContent = data.message;
                    if (data.tokenUpdated) {
                        refreshTokenButton.disabled = true;
                    }
                } else {
                    refreshStatus.textContent = 'Ошибка при проверке токена';
                }
            })
            .catch(error => {
                refreshStatus.textContent = 'Ошибка при проверке токена';
            });
    }
    refreshTokenButton.addEventListener('click', function () {
        fetch('http://localhost:8000/callback.php?action=refresh_token')
            .then(response => response.json())
            .then(data => {
                refreshStatus.textContent = data.message;
                if (data.status === 'success') {
                    refreshTokenButton.disabled = true;
                }
            })
            .catch(error => {
                refreshStatus.textContent = 'Ошибка при обновлении токена';
            });
    });

    checkAndUpdateToken();
});
