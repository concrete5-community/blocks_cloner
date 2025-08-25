import {setElementHighlighted} from './highlighter';
import {hook as hookMenus} from './menu';
import {findParentArea, getPageStructure, getPageStructureStartingAt} from './page-structure';
import conversion from './Conversion';
import Xml from './Xml';
import diff from './diff';
import * as service from './service';

if ((window as any).ccmBlocksCloner === undefined) {
  (window as any).ccmBlocksCloner = {
    getPageStructure,
    getPageStructureStartingAt,
    findParentArea,
    setElementHighlighted,
    conversion,
    xml: Xml,
    diff,
    service,
  };
  hookMenus();
}
