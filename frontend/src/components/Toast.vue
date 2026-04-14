<template>
  <div v-if="show" class="toast" :class="type">
    <div class="toast-content">
      <div class="toast-icon">
        <svg v-if="type === 'success'" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <svg v-else-if="type === 'error'" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <div class="toast-text">
        <h4>{{ title }}</h4>
        <p>{{ message }}</p>
      </div>
    </div>
    <button class="toast-close" @click="close">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  show: Boolean,
  type: {
    type: String,
    default: 'info'
  },
  title: String,
  message: String,
  duration: {
    type: Number,
    default: 5000
  }
});

const emit = defineEmits(['close']);

const close = () => {
  emit('close');
};

// Auto close after duration
if (props.show && props.duration > 0) {
  setTimeout(() => {
    close();
  }, props.duration);
}
</script>

<style scoped>
.toast {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 1000;
  min-width: 320px;
  max-width: 480px;
  background: var(--bg-card);
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  border: 1px solid var(--border-light);
  backdrop-filter: blur(10px);
  animation: slideIn 0.3s ease-out;
}

.toast.success {
  border-color: #10b981;
}

.toast.success .toast-icon {
  background: #10b981;
  color: white;
}

.toast.error {
  border-color: #ef4444;
}

.toast.error .toast-icon {
  background: #ef4444;
  color: white;
}

.toast.info .toast-icon {
  background: var(--primary-color);
  color: white;
}

.toast-content {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem;
}

.toast-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.toast-icon svg {
  width: 18px;
  height: 18px;
}

.toast-text h4 {
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
}

.toast-text p {
  margin: 0;
  font-size: 0.75rem;
  color: var(--text-secondary);
  line-height: 1.4;
}

.toast-close {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: none;
  border: none;
  width: 24px;
  height: 24px;
  border-radius: 6px;
  color: var(--text-secondary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.toast-close:hover {
  background: rgba(0, 0, 0, 0.1);
  color: var(--text-primary);
}

.toast-close svg {
  width: 14px;
  height: 14px;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@media (max-width: 480px) {
  .toast {
    left: 1rem;
    right: 1rem;
    min-width: auto;
  }
}
</style>
