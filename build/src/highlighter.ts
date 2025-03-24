let currentItemHighlighed: HTMLElement | null = null;

const HIGHLIGHTED_STYLE: Map<keyof CSSStyleDeclaration, string> = new Map([
  ['outline', '1px solid red'],
  ['boxShadow', '0 0 3px 3px #ff0000'],
  ['transition', 'box-shadow 0.5s, outline 0.5s'],
]);

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
      HIGHLIGHTED_STYLE.forEach((newStyleValue, styleProperty) => {
        element.dataset[`blocksClonerRestore${styleProperty}`] = element.style[styleProperty] as string;
        (element.style as any)[styleProperty] = newStyleValue;
      });
      element.dataset.blocksClonerHighlighted = '1';
      currentItemHighlighed = element;
    } else {
      Array.from(HIGHLIGHTED_STYLE.keys()).forEach((styleProperty) => {
        const restoreStyleValue = element.dataset[`blocksClonerRestore${styleProperty}`];
        if (restoreStyleValue !== undefined) {
          delete element.dataset[`blocksClonerRestore${styleProperty}`];
          (element.style as any)[styleProperty] = restoreStyleValue;
        }
      });
      delete element.dataset.blocksClonerHighlighted;
      currentItemHighlighed = null;
    }
  }
  if (currentItemHighlighed && ensureVisible) {
    tryScrollIntoView(currentItemHighlighed);
  }
}
