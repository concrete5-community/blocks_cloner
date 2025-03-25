import {addCurrentEnvironmentComment, extractEnvironment, getCurrentEnvironment} from './environment';
import {setElementHighlighted} from './highlighter';
import {hook as hookMenus} from './menu';
import {getPageStructure} from './page-structure';
import Core8To9Converter from './Conversion/Converter/Core8To9';
import Conversion from './Conversion';

if ((window as any).ccmBlocksCloner === undefined) {
  Conversion.registerConverter(Core8To9Converter);

  (window as any).ccmBlocksCloner = {
    getPageStructure,
    setElementHighlighted,
    envirorment: {
      getCurrent: getCurrentEnvironment,
      addCurrentToXml: addCurrentEnvironmentComment,
      extractFromXml: extractEnvironment,
    },
    conversion: Conversion,
  };
  hookMenus();
}
