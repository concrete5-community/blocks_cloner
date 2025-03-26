import {type CoreConverter} from '../Converter';

const converter: CoreConverter = {
  name: 'concrete5 v8 to ConcreteCMS v9',
  applicableTo: {
    sourceVersionConstraint: '^8',
    destinationVersionConstraint: '>=9',
  },
  blockTypes: {
    event_list: {
      addMissingFields: {
        btEventList: {
          titleFormat: 'h5',
        },
      },
    },
    express_entry_list: {
      addMissingFields: {
        btExpressEntryList: {
          titleFormat: 'h2',
        },
      },
    },
    feature: {
      addMissingFields: {
        btFeature: {
          titleFormat: 'h4',
        },
      },
      fontAwesome4to5Fields: {
        btFeature: ['icon'],
      },
    },
    google_map: {
      addMissingFields: {
        btGoogleMap: {
          titleFormat: 'h3',
        },
      },
    },
    page_list: {
      addMissingFields: {
        btPageList: {
          titleFormat: 'h5',
        },
      },
    },
    rss_displayer: {
      addMissingFields: {
        btRssDisplay: {
          titleFormat: 'h5',
        },
      },
    },
    tags: {
      addMissingFields: {
        btTags: {
          titleFormat: 'h5',
        },
      },
    },
    topic_list: {
      addMissingFields: {
        btTopicList: {
          titleFormat: 'h5',
        },
      },
    },
  },
};

export default converter;
