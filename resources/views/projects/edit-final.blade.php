<script>
// DEFINITIEVE DRAG & DROP OPLOSSING - SIMPEL EN WERKEND
document.addEventListener('DOMContentLoaded', function() {
    // Wacht op Sortable
    function waitForSortable() {
        if (typeof Sortable !== 'undefined') {
            initializeFinalDragDrop();
        } else {
            setTimeout(waitForSortable, 100);
        }
    }
    waitForSortable();
});

function initializeFinalDragDrop() {
    console.log('=== FINAL DRAG & DROP SOLUTION ===');
    
    // Voeg handles toe
    addDragHandles();
    
    // Initialiseer ALLEEN milestone sorting - simpel en werkend
    initMilestones();
    
    // Initialiseer task sorting PER milestone - geen conflicten
    initTasks();
    
    console.log('Drag & drop ready!');
}

function addDragHandles() {
    // Milestone handles
    document.querySelectorAll('.milestone-item').forEach(milestone => {
        if (!milestone.querySelector('.milestone-handle')) {
            const handle = document.createElement('div');
            handle.className = 'milestone-handle cursor-grab bg-gray-400 hover:bg-gray-600 rounded px-2 py-1 mr-2';
            handle.innerHTML = '≡≡≡';
            handle.style.cssText = 'font-size: 14px; font-weight: bold; user-select: none;';
            handle.title = 'Sleep milestone';
            milestone.insertBefore(handle, milestone.firstChild);
        }
    });
    
    // Task handles  
    document.querySelectorAll('[data-task-id]').forEach(task => {
        if (!task.querySelector('.task-handle')) {
            const handle = document.createElement('div');
            handle.className = 'task-handle cursor-grab bg-blue-400 hover:bg-blue-600 rounded px-2 py-1 mr-2';
            handle.innerHTML = '≡≡';
            handle.style.cssText = 'font-size: 12px; font-weight: bold; user-select: none; color: white;';
            handle.title = 'Sleep task';
            task.insertBefore(handle, task.firstChild);
        }
    });
}

function initMilestones() {
    const container = document.getElementById('milestones-container');
    if (!container) return;
    
    new Sortable(container, {
        animation: 150,
        handle: '.milestone-handle',
        draggable: '.milestone-item',
        onEnd: function(evt) {
            const items = Array.from(container.querySelectorAll('.milestone-item'));
            const ids = items.map(item => parseInt(item.getAttribute('data-milestone-id'))).filter(id => !isNaN(id));
            console.log('Milestone order:', ids);
            
            // Server call
            fetch(`/projects/${getProjectId()}/reorder-milestones`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ milestone_ids: ids })
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    console.log('✅ Milestones reordered');
                } else {
                    console.error('❌ Milestone reorder failed');
                    location.reload();
                }
            });
        }
    });
    console.log('✅ Milestone sorting active');
}

function initTasks() {
    // Voor elke milestone apart - geen conflicten mogelijk
    document.querySelectorAll('.milestone-item').forEach(milestone => {
        const milestoneId = milestone.getAttribute('data-milestone-id');
        if (!milestoneId) return;
        
        const tasks = Array.from(document.querySelectorAll(`[data-task-id][data-milestone-id="${milestoneId}"]`));
        if (tasks.length < 2) return; // Geen zin in 1 task
        
        // Maak unieke container
        const taskContainer = document.createElement('div');
        taskContainer.id = `tasks-${milestoneId}`;
        taskContainer.style.display = 'contents'; // Invisible wrapper
        
        // Verplaats tasks naar container
        const parent = tasks[0].parentElement;
        parent.appendChild(taskContainer);
        tasks.forEach(task => taskContainer.appendChild(task));
        
        // Maak sortable
        new Sortable(taskContainer, {
            animation: 150,
            handle: '.task-handle',
            onEnd: function() {
                const taskElements = Array.from(taskContainer.querySelectorAll('[data-task-id]'));
                const taskIds = taskElements.map(t => parseInt(t.getAttribute('data-task-id'))).filter(id => !isNaN(id));
                console.log(`Tasks for milestone ${milestoneId}:`, taskIds);
                
                // Server call
                fetch(`/project-milestones/${milestoneId}/reorder-tasks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ task_ids: taskIds })
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        console.log(`✅ Tasks reordered for milestone ${milestoneId}`);
                    } else {
                        console.error(`❌ Task reorder failed for milestone ${milestoneId}`);
                        location.reload();
                    }
                });
                
                // Verplaats tasks terug naar originele parent
                taskElements.forEach(task => parent.appendChild(task));
            }
        });
        console.log(`✅ Tasks sorted for milestone ${milestoneId}`);
    });
}

function getProjectId() {
    return window.location.pathname.split('/')[2];
}

// Maak globally beschikbaar voor reinitialize
window.initializeFinalDragDrop = initializeFinalDragDrop;
</script>