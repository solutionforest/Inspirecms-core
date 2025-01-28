
document.addEventListener('alpine:init', () => {
    // Checkbox tree node
    // Toggle "indeterminate" state for parent checkbox under "[data-checkbox-treenode]"
    document.querySelectorAll('[data-checkbox-treenode]').forEach((el) => {
        el.addEventListener('change', (event) => {
            const groupKey = el.getAttribute('data-checkbox-treenode-group');
            const groupCheckbox = document.querySelector(`[data-checkbox-treenode="${groupKey}"] input[type='checkbox']`);
            const checkboxes = document.querySelectorAll(`[data-checkbox-treenode-group="${groupKey}"] input[type='checkbox']`);

            if (groupCheckbox) {
                let checked = 0;
                let indeterminate = 0;

                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        checked++;
                    } else if (checkbox.indeterminate) {
                        indeterminate++;
                    }
                });

                if (groupCheckbox.checked == true) {
                    // skip if groupCheckbox is already checked
                } else if (checked > 0 || indeterminate > 0) {
                    groupCheckbox.checked = false;
                    groupCheckbox.indeterminate = true;
                } else {
                    groupCheckbox.checked = false;
                    groupCheckbox.indeterminate = false;
                }
            }
        });
    });

    Alpine.data('nestedCheckboxTree', ({
        max = null,
        min = null,
    }) => ({
        max,
        min,
        selected: [],
        collapsed: [],
        collapse(key) {
            if (this.collapsed.includes(key)) {
                this.collapsed = this.collapsed.filter(item => item !== key);
            } else {
                this.collapsed.push(key);
            }
        },
        isCollapsed(key) {
            return this.collapsed.includes(key);
        },
        init() {
            this.$watch('selected', (value) => {
                let currentCount = Array.from(value).filter((key) => key !== null).length;
                if (this.max != null && currentCount > this.max) {
                    // Remove the first item
                    this.selected.shift();
                }
            });
            this.$nextTick(() => {
                // init indeterminate state
                this.initIndeterminate();
            });

        },
        initIndeterminate() {
            Array.from(this.selected).forEach((key) => {
                let ctn = document.querySelector(`[data-checkbox-treenode="${key}"]`);
                ctn?.dispatchEvent(new Event('change'));
            });
        }
    }));
});