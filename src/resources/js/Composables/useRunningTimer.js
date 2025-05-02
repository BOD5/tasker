import { ref, watch, onUnmounted, toRef } from 'vue';

export function useRunningTimer(activeTimer) {
  const runningDuration = ref('00:00:00');
  let intervalId = null;

  const updateRunningDuration = () => {
    const timer = activeTimer.value;
    if (timer?.started_at) {
      try {
        const start = new Date(timer.started_at);
        if (isNaN(start.getTime())) {
          runningDuration.value = '--:--:--';
          if (intervalId) clearInterval(intervalId);
          intervalId = null;
          return;
        }
        const now = new Date();
        const diffInSeconds = Math.max(0, Math.round((now.getTime() - start.getTime()) / 1000));
        const hours = Math.floor(diffInSeconds / 3600);
        const minutes = Math.floor((diffInSeconds % 3600) / 60);
        const seconds = diffInSeconds % 60;
        runningDuration.value = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
      } catch (e) {
        console.error('Error calculating duration in composable:', e);
        runningDuration.value = 'Error';
        if (intervalId) clearInterval(intervalId);
        intervalId = null;
      }
    } else {
      runningDuration.value = '00:00:00';
    }
  };

  watch(
    activeTimer,
    (newTimer) => {
      if (newTimer && newTimer.id && newTimer.started_at) {
        if (!intervalId) {
          updateRunningDuration();
          intervalId = setInterval(updateRunningDuration, 1000);
        }
      } else if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
        runningDuration.value = '00:00:00';
      }
    },
    { immediate: true, deep: true },
  );

  onUnmounted(() => {
    if (intervalId) {
      clearInterval(intervalId);
    }
  });

  return { runningDuration };
}
