import Xml from "./Xml";

export interface Environment {
  readonly core: string;
  readonly packages: Readonly<Record<string, string>>;
}

const XML_ENVIRONMENT_PREFIX = 'Environment:';

export function getCurrentEnvironment(): Environment | null {
  return window.ccmBlocksClonerI18N?._environment || null;
}

export function extractEnvironment(xml: string | XMLDocument): Environment | null {
  if (typeof xml === 'string') {
    return extractEnvironment(Xml.parse(xml, true));
  }
  let result: Environment | null = null;
  const walker = xml.createTreeWalker(xml.documentElement, NodeFilter.SHOW_COMMENT);
  while (walker.nextNode() !== null) {
    const environment = extractEnvironmentFromComment(walker.currentNode);
    if (environment !== null) {
      if (result !== null) {
        throw new Error('Multiple environment comments found');
      }
      result = environment;
    }
  }
  return result;
}

function addCurrentEnvironmentCommentToDoc(doc: XMLDocument): boolean {
  if (extractEnvironment(doc) !== null) {
    throw new Error('Environment comment already exists');
  }
  const commentContent = createEnvironmentCommentContent();
  if (commentContent.length === 0) {
    return false;
  }
  const comment = doc.createComment(commentContent);
  document.documentElement.appendChild(comment);
  return true;
}

function addCurrentEnvironmentCommentToXml(xml: string): string {
  if (extractEnvironment(xml) !== null) {
    throw new Error('Environment comment already exists');
  }
  const commentContent = createEnvironmentCommentContent();
  if (commentContent.length > 0) {
    const doc = new DOMParser().parseFromString('<root />', 'application/xml');
    const comment = doc.createComment(commentContent);
    xml = xml.trimEnd() + '\n' + new XMLSerializer().serializeToString(comment);
  }
  return xml;
}

export function addCurrentEnvironmentComment<T extends string | XMLDocument>(xml: T): T extends string ? string : boolean {
  if (typeof xml === 'string') {
    return addCurrentEnvironmentCommentToXml(xml) as T extends string ? string : boolean;
  }
  return addCurrentEnvironmentCommentToDoc(xml) as T extends string ? string : boolean;
}

function extractEnvironmentFromComment(comment: Node): Environment | null {
  if (comment.nodeType !== Node.COMMENT_NODE) {
    return null;
  }
  const commentText: string = (comment.textContent || '').trim();
  if (!commentText.startsWith(XML_ENVIRONMENT_PREFIX)) {
    return null;
  }
  try {
    const parsed: any = JSON.parse(commentText.substring(XML_ENVIRONMENT_PREFIX.length).trim());
    if (typeof parsed !== 'object' || parsed === null) {
      throw new Error('Invalid environment comment');
    }
    const core: string = typeof parsed.core === 'string' ? parsed.core.trim() : '';
    if (core.length === 0) {
      throw new Error('Missing core');
    }
    const packages: Record<string, string> = {};
    if (!(parsed.packages instanceof Array) || parsed.packages.length !== 0) {
      if (typeof parsed.packages !== 'object' || parsed.packages === null) {
        throw new Error('Missing packages');
      }
      for (const key in parsed.packages) {
        if (key.length === 0 || typeof parsed.packages[key] !== 'string') {
          throw new Error('Invalid package');
        }
        packages[key] = parsed.packages[key].trim();
        if (packages[key].length === 0) {
          throw new Error('Invalid package');
        }
      }
    }
    return {
      core,
      packages,
    };
  } catch (e) {
    console.error('Failed to parse environment comment', e);
    return null;
  }
}

function createEnvironmentCommentContent(): string {
  const environment = getCurrentEnvironment();

  return environment === null ? '' : ` ${XML_ENVIRONMENT_PREFIX} ${JSON.stringify(environment)} `;
}
