export function localize(key: string): string | null {
  const result = window.ccmBlocksClonerI18N?.[key];
  return typeof result === 'string' ? result : null;
}

export function getBlockTypeName(handle: string): string | null {
  return window.ccmBlocksClonerI18N?.blockTypeNames[handle] || null;
}
