/**
 * Attach drag & drop file support to a container
 */
export function attachDragDrop(el, { onEnter, onLeave, onDrop }) {
    if (!el) return;
  
    // Dragover
    el.addEventListener('dragover', e => {
      e.preventDefault();
      el.classList.add('-is-dragover');
      if (typeof onEnter === 'function') onEnter(el);
    });
  
    // Dragleave
    el.addEventListener('dragleave', e => {
      e.preventDefault();
      el.classList.remove('-is-dragover');
      if (typeof onLeave === 'function') onLeave(el);
    });
  
    // Drop
    el.addEventListener('drop', e => {
      e.preventDefault();
      el.classList.remove('-is-dragover');
  
      const files = Array.from(e.dataTransfer.files || []);
      if (!files.length) return;
  
      if (typeof onDrop === 'function') onDrop(files, el);
    });
  }
  