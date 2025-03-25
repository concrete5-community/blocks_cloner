import {addCurrentEnvironmentComment, extractEnvironment, getCurrentEnvironment} from './environment';
import {setElementHighlighted} from './highlighter';
import {hook as hookMenus} from './menu';
import {getPageStructure} from './page-structure';

if ((window as any).ccmBlocksCloner === undefined) {
  (window as any).ccmBlocksCloner = {
    getPageStructure,
    setElementHighlighted,
    envirorment: {
      getCurrent: getCurrentEnvironment,
      addCurrentToXml: addCurrentEnvironmentComment,
      extractFromXml: extractEnvironment,
    },
  };
  hookMenus();
}
