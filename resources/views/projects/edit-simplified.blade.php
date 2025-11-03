# This is a backup script section for the project edit page

# The complete script section that needs to replace lines 1851-3593 in edit.blade.php:

<script>
// Initialize SortableJS for drag & drop functionality
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

// Reorder milestones
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
        
        showSuccessMessage('Milestones reordered successfully');
    } catch (error) {
        console.error('Error reordering milestones:', error);
        showErrorMessage('Failed to reorder milestones');
        location.reload();
    }
}

// Reorder tasks
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
        
        showSuccessMessage('Tasks reordered successfully');
    } catch (error) {
        console.error('Error reordering tasks:', error);
        showErrorMessage('Failed to reorder tasks');
        location.reload();
    }
}

// Utility functions
function showSuccessMessage(message) {
    const div = document.createElement('div');
    div.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    div.textContent = message;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 3000);
}

function showErrorMessage(message) {
    const div = document.createElement('div');
    div.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    div.textContent = message;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 5000);
}

// Make functions available globally for Alpine.js component
window.initializeDragDrop = initializeDragDrop;
window.reorderMilestones = reorderMilestones;
window.reorderTasks = reorderTasks;
</script>