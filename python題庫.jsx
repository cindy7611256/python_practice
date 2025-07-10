fetch(`https://sheets.googleapis.com/v4/spreadsheets/${spreadsheetId}/values/${sheetName}?key=${apiKey}`)
  .then(res => res.json())
  .then(data => {
    const rows = data.values; // 第一列是標題
  });

