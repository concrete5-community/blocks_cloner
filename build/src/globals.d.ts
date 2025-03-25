import type {Environment} from './environment';

declare global {
  interface Window {
    readonly CCM_CID: number;
    readonly CCM_DISPATCHER_FILENAME: string;

    readonly ConcreteEvent?: {
      subscribe(event: string, callback: (e: any, args: any) => void): void;
    };

    ccmBlocksClonerI18N?: Readonly<Record<string, string>> & {
      readonly _blockTypeNames: Readonly<Record<string, string>>;
      readonly _environment: Environment;
    };
  }
}

declare global {
  interface JQuery {
    dialog(): JQuery;
  }
}

export {};
