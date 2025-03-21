declare global {
  interface Window {
    ccmBlocksClonerI18N?: {
      blockTypeNames: Record<string, string>;
    } & Record<string, string>;
    ConcreteEvent?: {
      subscribe(event: string, callback: (e: any, args: any) => void): void;
    };
    CCM_DISPATCHER_FILENAME: string;
    CCM_CID: number;
  }
}

declare global {
  interface JQuery {
    dialog(): JQuery;
  }
}

export {};
