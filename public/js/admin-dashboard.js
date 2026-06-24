(function(){
  function decode(el, key){
    const s = el.dataset[key];
    try{ return JSON.parse(atob(s)); }catch(e){ return []; }
  }

  const el = document.getElementById('dashboard-data');
  if(!el) return;

  const chartLabels = decode(el, 'chartLabels');
  const chartSignups = decode(el, 'chartSignups');
  const chartGens = decode(el, 'chartGens');
  const ratingLabels = decode(el, 'ratingLabels');
  const ratingValues = decode(el, 'ratingValues');
  const tierLabels = decode(el, 'tierLabels');
  const tierValues = decode(el, 'tierValues');
  const platLabels = decode(el, 'platLabels');
  const platValues = decode(el, 'platValues');

  const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { labels: { color: '#94a3b8', font: { size: 11 } } } },
    scales: {
      x: { ticks: { color: '#94a3b8', font: { size: 10 } }, grid: { color: 'rgba(148,163,184,0.06)' } },
      y: { ticks: { color: '#94a3b8', font: { size: 10 } }, grid: { color: 'rgba(148,163,184,0.06)' } }
    }
  };

  try{
    new Chart(document.getElementById('signupChart'), {
      type: 'line',
      data: { labels: chartLabels, datasets: [{ label: 'New Signups', data: chartSignups, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.08)', fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#6366f1' }] },
      options: commonOptions
    });

    new Chart(document.getElementById('genChart'), {
      type: 'bar',
      data: { labels: chartLabels, datasets: [{ label: 'Generations', data: chartGens, backgroundColor: '#8b5cf6', borderRadius: 4 }] },
      options: commonOptions
    });

    new Chart(document.getElementById('ratingChart'), {
      type: 'bar',
      data: { labels: ratingLabels, datasets: [{ label: 'Ratings', data: ratingValues, backgroundColor: ['#ef4444','#f97316','#eab308','#84cc16','#22c55e'], borderRadius: 4 }] },
      options: commonOptions
    });

    new Chart(document.getElementById('tierChart'), {
      type: 'doughnut',
      data: { labels: tierLabels, datasets: [{ data: tierValues, backgroundColor: ['#64748b','#6366f1','#8b5cf6','#10b981'], borderWidth: 0 }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { color: '#94a3b8', font: { size: 11 } } } } }
    });

    new Chart(document.getElementById('platChart'), {
      type: 'bar',
      data: { labels: platLabels, datasets: [{ label: 'Generations', data: platValues, backgroundColor: ['#6366f1','#8b5cf6','#ec4899','#10b981','#f59e0b','#3b82f6'], borderRadius: 4 }] },
      options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { ticks: { color: '#94a3b8', font: { size: 10 } }, grid: { color: 'rgba(148,163,184,0.06)' } }, y: { ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { display: false } } } }
    });
  }catch(e){ console.error('admin-dashboard init error', e); }
  // apply data-width attributes to progress bars
  (function applyBarWidths(){
    try{
      document.querySelectorAll('[data-width]').forEach(el => {
        const v = el.getAttribute('data-width');
        if(!v) return;
        const n = Math.min(100, Number(v));
        el.style.width = n + '%';
      });
    }catch(e){/* ignore */}
  })();
})();
