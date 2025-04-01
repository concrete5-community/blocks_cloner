export function localize(key: string, fallback: string): string {
  return window.ccmBlocksClonerDynamicData?.i18n[key] || fallback;
}

export function getBlockTypeName(handle: string): string | null {
  return window.ccmBlocksClonerDynamicData?.blockTypeNames[handle] || null;
}
