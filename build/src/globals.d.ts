import type JQuery from 'jquery';

interface DynamicData {
  readonly i18n: Record<string, string>;
  readonly stackEditPageID: number | null;
  readonly blockTypeNames: Readonly<Record<string, string>>;
}

declare global {
  interface Window {
    readonly CCM_CID: number;
    readonly CCM_DISPATCHER_FILENAME: string;
    readonly Concrete: {
      getEditMode(): {
        getAreaByID(id: number): any;
        getBlockByID(id: number): any;
      };
    };
    readonly ConcreteEvent?: {
      subscribe(event: string, callback: (e: any, args: any) => void): void;
    };
    readonly ConcreteMenuManager: {
      enabled: boolean;
      $clickProxy: JQuery;
      getActiveMenu():
        | {
            hide(): void;
          }
        | null
        | undefined
        | false;
    };
    readonly ccmBlocksClonerDynamicData?: DynamicData;
  }
}

declare global {
  interface JQuery {
    dialog(): JQuery;
  }
}

export {};
