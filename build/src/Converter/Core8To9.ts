import {type CoreConverter} from '../Conversion/Converter';

const converter: CoreConverter = {
  name: 'concrete5 v8 to ConcreteCMS v9',
  applicableTo: {
    sourceVersionConstraint: '^8',
    destinationVersionConstraint: '^9',
  },
};

export default converter;
