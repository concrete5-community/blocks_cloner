import {getAllConverters, getConvertersForEvironments, registerConverter} from './Conversion/Converters';
import {addCurrentEnvironmentComment, extractEnvironment, getCurrentEnvironment} from './environment';
import {setElementHighlighted} from './highlighter';
import {hook as hookMenus} from './menu';
import {getPageStructure} from './page-structure';
import Core8To9Converter from './Converter/Core8To9';

if ((window as any).ccmBlocksCloner === undefined) {
  registerConverter(Core8To9Converter);

  (window as any).ccmBlocksCloner = {
    getPageStructure,
    setElementHighlighted,
    envirorment: {
      getCurrent: getCurrentEnvironment,
      addCurrentToXml: addCurrentEnvironmentComment,
      extractFromXml: extractEnvironment,
    },
    conversion: {
      registerConverter,
      getAllConverters,
      getConvertersForEvironments,
    },
  };
  hookMenus();
}
