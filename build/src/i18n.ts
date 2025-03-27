export function localize(key: string): string | null {
  return window.ccmBlocksClonerDynamicData?.i18n[key] || null;
}

export function getBlockTypeName(handle: string): string | null {
  return window.ccmBlocksClonerDynamicData?.blockTypeNames[handle] || null;
}
