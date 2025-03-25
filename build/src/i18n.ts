export function localize(key: string): string | null {
  const result: any = window.ccmBlocksClonerI18N?.[key];
  return typeof result === 'string' ? result : null;
}

export function getBlockTypeName(handle: string): string | null {
  return window.ccmBlocksClonerI18N?._blockTypeNames[handle] || null;
}
