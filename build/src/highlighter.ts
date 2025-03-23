let currentItemHighlighed: HTMLElement | null = null;

type StyleKey = keyof CSSStyleDeclaration;

const HIGHLIGHTED_STYLE: Partial<Record<StyleKey, string | number>> = {
  outline: '1px solid red',
  boxShadow: '0 0 3px 3px #ff0000',
  transition: 'box-shadow 0.5s, outline 0.5s',
};

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
    const elementStyle: any = element.style;
    if (highlight) {
      for (const [key, value] of Object.entries(HIGHLIGHTED_STYLE)) {
        element.dataset[`blocksClonerRestore${key}`] = (elementStyle[key] || '').toString();
        elementStyle[key] = value;
      }
      element.dataset.blocksClonerHighlighted = '1';
      currentItemHighlighed = element;
    } else {
      Object.keys(HIGHLIGHTED_STYLE).forEach((key) => {
        if (element.dataset[`blocksClonerRestore${key}`] !== undefined) {
          elementStyle[key] = element.dataset[`blocksClonerRestore${key}`];
        }
        delete element.dataset[`blocksClonerRestore${key}`];
      });
      delete element.dataset.blocksClonerHighlighted;
      currentItemHighlighed = null;
    }
  }
  if (currentItemHighlighed && ensureVisible) {
    tryScrollIntoView(currentItemHighlighed);
  }
}
