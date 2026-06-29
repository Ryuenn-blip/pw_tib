</div><!-- .main-wrapper -->
<script>
// Lightweight admin progress bar (no full loader)
(function(){
    var p = document.createElement('div');
    p.id = 'gs-progress';
    document.body.appendChild(p);
    var v = 0, t = setInterval(function(){
        v = Math.min(v + (v < 30 ? 10 : v < 70 ? 4 : 1.5), 92);
        p.style.width = v + '%';
    }, 80);
    window.addEventListener('load', function(){
        clearInterval(t);
        p.style.width = '100%';
        setTimeout(function(){ p.classList.add('done'); }, 300);
    });
})();
</script>
<script src="assets/js/admin.js"></script>
</body>
</html>
