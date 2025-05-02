export function useDateTimeFormatters() {
  const formatDateTime = (dateTimeString) => {
    if (!dateTimeString) return '-';
    try {
      return new Date(dateTimeString).toLocaleString('uk-UA', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
      });
    } catch (e) {
      console.error('DateTime formatting error:', dateTimeString, e);
      return 'Error';
    }
  };

  const calculateDuration = (start, end) => {
    if (!start || !end) return '-';
    try {
      const startDate = new Date(start);
      const endDate = new Date(end);
      if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) return '--:--';
      const diffInSeconds = Math.max(0, Math.round((endDate - startDate) / 1000));
      const hours = Math.floor(diffInSeconds / 3600);
      const minutes = Math.floor((diffInSeconds % 3600) / 60);
      return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
    } catch (e) {
      console.error('Duration calculation error:', start, end, e);
      return 'Error';
    }
  };

  const formatForInput = (dateTimeString) => {
    if (!dateTimeString) return '';
    try {
      const date = new Date(dateTimeString);
      if (isNaN(date.getTime())) {
        return '';
      }
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      const hours = String(date.getHours()).padStart(2, '0');
      const minutes = String(date.getMinutes()).padStart(2, '0');
      return `${year}-${month}-${day}T${hours}:${minutes}`;
    } catch (e) {
      console.error('Format for input error:', dateTimeString, e);
      return '';
    }
  };

  const formatDate = (dateTimeString) => {
    if (!dateTimeString) return '-';
    try {
      return new Date(dateTimeString).toLocaleDateString('uk-UA', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
      });
    } catch (e) {
      console.error('Date format error:', dateTimeString, e);
      return 'Error';
    }
  };

  const dateToUTC = (date) => {
    try {
      if (date) {
        const startDate = new Date(date);
        if (!isNaN(startDate.getTime())) {
          return startDate.toISOString();
        }
      }
    } catch (e) {
      console.error('Error parsing date:', date, e);
    }
    return date;
  };
  return { formatDateTime, calculateDuration, formatForInput, formatDate, dateToUTC };
}
