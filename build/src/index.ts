import {addCurrentEnvironmentComment, extractEnvironment, getCurrentEnvironment} from './environment';
import {setElementHighlighted} from './highlighter';
import {hook as hookMenus} from './menu';
import {findParentArea, getPageStructure, getPageStructureStartingAt} from './page-structure';
import Conversion from './Conversion';
import Xml from './Xml';

if ((window as any).ccmBlocksCloner === undefined) {
  (window as any).ccmBlocksCloner = {
    getPageStructure,
    getPageStructureStartingAt,
    findParentArea,
    setElementHighlighted,
    environment: {
      getCurrent: getCurrentEnvironment,
      addCurrentToXml: addCurrentEnvironmentComment,
      extractFromXml: extractEnvironment,
    },
    conversion: Conversion,
    xml: Xml,
  };
  hookMenus();
}
