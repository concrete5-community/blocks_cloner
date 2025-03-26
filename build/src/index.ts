import {addCurrentEnvironmentComment, extractEnvironment, getCurrentEnvironment} from './environment';
import {setElementHighlighted} from './highlighter';
import {hook as hookMenus} from './menu';
import {findParentArea, getPageStructure} from './page-structure';
import Core8To9Converter from './Conversion/Converter/Core8To9';
import ThemePixel2To9Converter from './Conversion/Converter/ThemePixel2To9';
import Conversion from './Conversion';
import Xml from './Xml';

if ((window as any).ccmBlocksCloner === undefined) {
  Conversion.registerConverters([Core8To9Converter, ThemePixel2To9Converter]);

  (window as any).ccmBlocksCloner = {
    getPageStructure,
    findParentArea,
    setElementHighlighted,
    envirorment: {
      getCurrent: getCurrentEnvironment,
      addCurrentToXml: addCurrentEnvironmentComment,
      extractFromXml: extractEnvironment,
    },
    conversion: Conversion,
    xml: Xml,
  };
  hookMenus();
}
