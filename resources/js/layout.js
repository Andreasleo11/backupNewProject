/**
 * Layout Alpine Components & Toast System
 */

// __toastAdd and __toastQueue are now initialized in app.blade.php <head> 
// to prevent timing issues with Vite module loading.

// Register Livewire.on as soon as Livewire is available
document.addEventListener('livewire:init', function () {
    if (window.Livewire) {
        Livewire.on('toast', function (params) {
            window.__toastAdd(params);
        });
        Livewire.on('flash', function (params) {
            window.__toastAdd(params);
        });
    }
});

// Capture window-level toast events (from Alpine $dispatch or manual JS)
window.addEventListener('toast', function (e) {
    window.__toastAdd(e.detail);
});

// Register Alpine components safely
function registerAlpineComponents() {
    if (window.__alpineComponentsRegistered) return;
    window.__alpineComponentsRegistered = true;

    // Toast Notification Manager
    Alpine.data('toastManager', function () {
        return {
            toasts: [],
            nextId: 1,

            init() {
                window.__toastHandler = (data) => this.addToast(data);
                window.__toastReady = true;
                window.__toastQueue.forEach(d => this.addToast(d));
                window.__toastQueue = [];
            },

            addToast(data) {
                const id = this.nextId++;
                const duration = data.duration || 5000;
                const toast = {
                    id,
                    type: data.type || 'info',
                    message: data.message || data.body || '',
                    visible: false,
                    progress: 100
                };
                this.toasts.push(toast);
                this.$nextTick(() => {
                    toast.visible = true;
                    const steps = duration / 100;
                    let step = 0;
                    toast._timer = setInterval(() => {
                        step++;
                        toast.progress = 100 - (step / steps * 100);
                        if (step >= steps) {
                            clearInterval(toast._timer);
                            this.removeToast(id);
                        }
                    }, 100);
                });
            },

            removeToast(id) {
                const t = this.toasts.find(t => t.id === id);
                if (!t) return;
                if (t._timer) clearInterval(t._timer);
                t.visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 400);
            },

            icon(type) {
                return {
                    success: `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`,
                    error: `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>`,
                    warning: `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`,
                    info: `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
                }[type] || `<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
            }
        };
    });

    // Navigation Item Component
    Alpine.data('navItem', (isMobile, idx) => ({
        hover: false,
        flyoutTop: 0,
        myIdx: idx,
        pinned: false,
        pinLoading: false,
        flyoutTimer: null,

        handleMouseEnter() {
            if (isMobile) return;
            clearTimeout(this.flyoutTimer);
            this.flyoutTimer = setTimeout(() => {
                this.hover = true;
                this.flyoutTop = this.$el.getBoundingClientRect().top;
                this.$dispatch('sbflyout', { idx: this.myIdx });
                this.$nextTick(() => {
                    const el = document.getElementById('flyout-' + this.myIdx);
                    if (el) {
                        const rect = el.getBoundingClientRect();
                        if (rect.bottom > window.innerHeight - 20) {
                            this.flyoutTop = Math.max(20, window.innerHeight - rect.height - 20);
                        }
                    }
                });
            }, 125);
        },

        handleMouseLeave() {
            if (isMobile) return;
            clearTimeout(this.flyoutTimer);
            this.hover = false;
        },

        handleFlyoutWindow(e) {
            if (e.detail.idx !== this.myIdx) {
                clearTimeout(this.flyoutTimer);
                this.hover = false;
            }
        },

        togglePin(routeName) {
            this.pinLoading = true;
            if (this.pinned) {
                this.$dispatch('nav-unpin', { routeName: routeName });
            } else {
                this.$dispatch('nav-pin', { routeName: routeName });
            }
            setTimeout(() => this.pinLoading = false, 600);
        },

        handlePinStateChangedWindow(e, routeName) {
            if (e.detail.routeName === routeName) this.pinned = e.detail.pinned;
        }
    }));

    // Navigation Group Component
    Alpine.data('navGroup', (isMobile, idx, defaultOpen) => ({
        hover: false,
        open: defaultOpen,
        flyoutOpen: false,
        flyoutTop: 0,
        flyoutTimer: null,
        myIdx: idx,

        init() {
            if (this.open) {
                this.$nextTick(() => this.$el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }));
            }
        },

        handleMouseEnter() {
            if (isMobile) return;
            clearTimeout(this.flyoutTimer);
            this.flyoutTimer = setTimeout(() => {
                this.hover = true;
                this.flyoutOpen = true;
                this.flyoutTop = this.$el.getBoundingClientRect().top;
                this.$dispatch('sbflyout', { idx: this.myIdx });
                this.$nextTick(() => {
                    const el = document.getElementById('flyout-' + this.myIdx);
                    if (el) {
                        const rect = el.getBoundingClientRect();
                        if (rect.bottom > window.innerHeight - 20) {
                            this.flyoutTop = Math.max(20, window.innerHeight - rect.height - 20);
                        }
                    }
                });
            }, 125);
        },

        handleMouseLeave() {
            if (isMobile) return;
            clearTimeout(this.flyoutTimer);
            this.flyoutTimer = setTimeout(() => {
                this.flyoutOpen = false;
                this.hover = false;
            }, 150);
        },

        handleFlyoutWindow(e) {
            if (e.detail.idx !== this.myIdx) {
                clearTimeout(this.flyoutTimer);
                this.flyoutOpen = false;
                this.hover = false;
            }
        }
    }));

    // Navigation Child Component
    Alpine.data('navChild', (routeName) => ({
        pinned: false,
        pinLoading: false,
        routeName: routeName,

        togglePin() {
            this.pinLoading = true;
            if (this.pinned) {
                this.$dispatch('nav-unpin', { routeName: this.routeName });
            } else {
                this.$dispatch('nav-pin', { routeName: this.routeName });
            }
            setTimeout(() => this.pinLoading = false, 600);
        },

        handlePinStateChangedWindow(e) {
            if (e.detail.routeName === this.routeName) this.pinned = e.detail.pinned;
        }
    }));
}

if (window.Alpine) {
    registerAlpineComponents();
} else {
    document.addEventListener('alpine:init', registerAlpineComponents);
}
