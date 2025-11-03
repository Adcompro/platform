<script>
// Simple drag & drop initialization - everything else handled by Alpine.js
document.addEventListener('DOMContentLoaded', function() {
    initializeDragDrop();
});

function initializeDragDrop() {
    // Initialize milestone sorting
    const milestonesList = document.getElementById('milestones-list');
    if (milestonesList && typeof Sortable !== 'undefined') {
        new Sortable(milestonesList, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function(evt) {
                const milestoneIds = Array.from(milestonesList.children).map(item => 
                    parseInt(item.dataset.milestoneId)
                );
                reorderMilestones(milestoneIds);
            }
        });
    }

    // Initialize task sorting for each milestone
    document.querySelectorAll('.tasks-list').forEach(tasksList => {
        if (typeof Sortable !== 'undefined') {
            new Sortable(tasksList, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function(evt) {
                    const taskIds = Array.from(tasksList.children).map(item => 
                        parseInt(item.dataset.taskId)
                    );
                    const milestoneId = tasksList.dataset.milestoneId;
                    reorderTasks(milestoneId, taskIds);
                }
            });
        }
    });
}

// Reorder functions
async function reorderMilestones(milestoneIds) {
    try {
        const projectId = window.location.pathname.split('/')[2];
        const response = await fetch(`/projects/${projectId}/reorder-milestones`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ milestone_ids: milestoneIds })
        });

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Failed to reorder milestones');
        }
    } catch (error) {
        console.error('Error reordering milestones:', error);
        location.reload();
    }
}

async function reorderTasks(milestoneId, taskIds) {
    try {
        const response = await fetch(`/project-milestones/${milestoneId}/reorder-tasks`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ task_ids: taskIds })
        });

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Failed to reorder tasks');
        }
    } catch (error) {
        console.error('Error reordering tasks:', error);
        location.reload();
    }
}

// Make functions available globally
window.initializeDragDrop = initializeDragDrop;
window.reorderMilestones = reorderMilestones;
window.reorderTasks = reorderTasks;
</script>