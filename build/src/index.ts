import {setElementHighlighted} from './highlighter';
import {hook as hookMenus} from './menu';
import {findParentArea, getPageStructure, getPageStructureStartingAt} from './page-structure';
import Conversion from './Conversion';
import Xml from './Xml';
import diff from './diff';

if ((window as any).ccmBlocksCloner === undefined) {
  (window as any).ccmBlocksCloner = {
    getPageStructure,
    getPageStructureStartingAt,
    findParentArea,
    setElementHighlighted,
    conversion: Conversion,
    xml: Xml,
    diff,
  };
  hookMenus();
}
