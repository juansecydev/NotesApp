<script>
    (() => {
        let savedTheme = null;

        try {
            savedTheme = localStorage.getItem('notes-theme');
        } catch {
            savedTheme = null;
        }

        document.documentElement.setAttribute('data-bs-theme', savedTheme === 'dark' ? 'dark' : 'light');
    })();
</script>
