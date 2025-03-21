let currentItemHighlighed: HTMLElement | null = null;

function checkElementInViewport(el: HTMLElement, callback: (isInViewport: boolean) => void): void {
  const onIntersection = (entries: IntersectionObserverEntry[], observer: IntersectionObserver): void => {
    callback(entries.some((entry) => entry.isIntersecting));
    observer.disconnect();
  };
  const options = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1,
  };
  const observer = new IntersectionObserver(onIntersection, options);
  observer.observe(el);
}

function tryScrollIntoView(el: HTMLElement): void {
  if (!el.scrollIntoView) {
    return;
  }
  checkElementInViewport(el, (isVisible) => {
    if (!isVisible) {
      try {
        el.scrollIntoView({behavior: 'smooth', block: 'nearest', inline: 'start'});
      } catch {}
    }
  });
}

export function setElementHighlighted(element: HTMLElement, highlight: boolean, ensureVisible: boolean = false) {
  if (highlight) {
    if (currentItemHighlighed === element) {
      if (ensureVisible) {
        tryScrollIntoView(currentItemHighlighed);
      }
      return;
    }
    if (currentItemHighlighed !== null) {
      setElementHighlighted(currentItemHighlighed, false);
    }
  }
  const wasHighlighted = element.dataset.blocksClonerHighlighted === '1';
  if (highlight !== wasHighlighted) {
    if (highlight) {
      element.dataset.blocksClonerRestoreOutline = element.style.outline || '';
      element.style.outline = '2px solid red';
      element.dataset.blocksClonerHighlighted = '1';
      currentItemHighlighed = element;
    } else {
      element.style.outline = element.dataset.blocksClonerRestoreOutline || '';
      delete element.dataset.blocksClonerHighlighted;
      delete element.dataset.blocksClonerRestoreOutline;
      currentItemHighlighed = null;
    }
  }
  if (currentItemHighlighed && ensureVisible) {
    tryScrollIntoView(currentItemHighlighed);
  }
}
