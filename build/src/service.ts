function checkErrorInJsonResponse(text: string, decodedData: any): void {
  if (!decodedData?.error) {
    return;
  }
  if (typeof decodedData.error === 'string') {
    throw new Error(decodedData.error);
  }
  if (decodedData.errors instanceof Array && typeof decodedData.errors[0] === 'string') {
    throw new Error(decodedData.errors[0]);
  }
  throw new Error(text);
}

export async function parseJsonResponse(response: Response): Promise<any> {
  const responseText = await response.text();
  let responseData: any;
  try {
    responseData = JSON.parse(responseText);
  } catch {
    throw new Error(responseText);
  }
  checkErrorInJsonResponse(responseText, responseData);
  if (!response.ok) {
    throw new Error(responseText);
  }
  return responseData;
}

export async function parseTextResponse(response: Response): Promise<string> {
  const responseText = await response.text();
  let responseData: any;
  try {
    responseData = JSON.parse(responseText);
  } catch {
    responseData = null;
  }
  checkErrorInJsonResponse(responseText, responseData);
  if (!response.ok) {
    throw new Error(responseText);
  }
  return responseText;
}
