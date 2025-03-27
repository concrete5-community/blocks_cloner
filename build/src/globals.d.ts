import type {Environment} from './environment';

interface DynamicData {
  readonly i18n: Record<string, string>;
  readonly blockTypeNames: Readonly<Record<string, string>>;
  readonly environment: Environment;
}

declare global {
  interface Window {
    readonly CCM_CID: number;
    readonly CCM_DISPATCHER_FILENAME: string;

    readonly ConcreteEvent?: {
      subscribe(event: string, callback: (e: any, args: any) => void): void;
    };

    ccmBlocksClonerDynamicData?: DynamicData;
  }
}

declare global {
  interface JQuery {
    dialog(): JQuery;
  }
}

export {};
