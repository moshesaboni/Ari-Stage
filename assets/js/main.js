document.addEventListener('DOMContentLoaded', function () {
    // Handle Create Lineup form submission
    const createLineupForm = document.getElementById('createLineupForm');
    if (createLineupForm) {
        createLineupForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const lineupName = document.getElementById('lineupName').value;
            // The submit button is outside the form, so we find it in the modal footer
            const createButton = this.closest('.modal-content').querySelector('.modal-footer button[type="submit"]');
            const originalButtonText = createButton.innerHTML;

            createButton.disabled = true;
            createButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> יוצר...';

            fetch('api/lineups_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'create', name: lineupName }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload(); // Reload to see the new lineup
                } else {
                    alert('שגיאה ביצירת הליינאפ: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('אירעה שגיאה בלתי צפויה.');
            })
            .finally(() => {
                // This might not be reached if the page reloads, but it's good practice
                createButton.disabled = false;
                createButton.innerHTML = originalButtonText;
            });
        });
    }
});
