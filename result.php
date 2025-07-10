<?php
// === 接收表單資料 ===
$target = $_POST['target'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$news_count = $_POST['news_count'] ?? 5;
$keyword_count = $_POST['keyword_count'] ?? 10;

// === Google Custom Search API 設定 ===
$API_KEY = 'AIzaSyBGWm6KJloQe_VWVFlvQNXI1naHFLxLoxA';
$CX = '87eb62feab98447c8';

// === 執行搜尋 ===
$search_results = [];
if (!empty($target)) {
    $query = urlencode($target);
    $url = "https://www.googleapis.com/customsearch/v1?key=$API_KEY&cx=$CX&q=$query&num=$news_count";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (!empty($data['items'])) {
        foreach ($data['items'] as $item) {
            $search_results[] = [
                'title' => $item['title'],
                'link' => $item['link'],
                'snippet' => $item['snippet']
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>分析結果 - KOL關鍵字分析工具</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    label { display: block; margin-top: 10px; }
    input[type="text"], input[type="email"] {
      width: 300px; padding: 5px; margin-top: 5px;
    }
    .keyword { margin: 5px 0; cursor: pointer; color: blue; }
    .keyword:hover { text-decoration: underline; }
    .keyword-details { display: none; margin-left: 20px; color: #555; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; }
    .download-btn { margin-top: 20px; }
  </style>
  <script>
    function toggleKeyword(id) {
      const elem = document.getElementById(id);
      elem.style.display = elem.style.display === 'none' ? 'block' : 'none';
    }

    function exportCSV() {
      const table = document.getElementById("news-table");
      let csv = "";
      for (let row of table.rows) {
        let cols = [...row.cells].map(cell => `"${cell.innerText.replace(/"/g, '""')}"`);
        csv += cols.join(",") + "\n";
      }

      const blob = new Blob([csv], { type: "text/csv" });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = "news_results.csv";
      link.click();
    }

    function exportPDF() {
      const table = document.getElementById("news-table").outerHTML;
      const newWindow = window.open('', '_blank');
      newWindow.document.write(`
        <html>
          <head><title>新聞結果 PDF</title></head>
          <body>
            ${table}
            <script>
              setTimeout(() => {
                window.print();
              }, 500);
            </script>
          </body>
        </html>
      `);
      newWindow.document.close();
    }
  </script>
</head>
<body>

<h2>分析對象基本資料</h2>
<p><strong>對象：</strong> <?= htmlspecialchars($target) ?></p>
<p><strong>資訊時期：</strong> <?= htmlspecialchars($start_date) ?> ~ <?= htmlspecialchars($end_date) ?></p>
<p><strong>熱門新聞條數：</strong> <?= htmlspecialchars($news_count) ?> 條</p>
<p><strong>關鍵字數量：</strong> <?= htmlspecialchars($keyword_count) ?> 個</p>

<hr>

<form>
  <label>本名：<input type="text" name="name"></label>
  <label>出生年月日：<input type="text" name="dob"></label>
  <label>星座：<input type="text" name="zodiac"></label>
  <label>喜好/興趣：<input type="text" name="interests"></label>
  <label>職業：<input type="text" name="job"></label>
  <label>經紀公司：<input type="text" name="agency"></label>
  <label>聯絡方式：<input type="text" name="contact"></label>
  <label>聯絡信箱：<input type="email" name="email"></label>
</form>

<hr>

<h2>相關關鍵字（範例顯示）</h2>
<div>
  <?php for ($i = 1; $i <= 5; $i++): ?>
    <div class="keyword" onclick="toggleKeyword('kw<?= $i ?>')">關鍵<?= $i ?> (<?= rand(10, 100) ?>次)</div>
    <div id="kw<?= $i ?>" class="keyword-details">這裡是關鍵<?= $i ?> 的詳細資料表格</div>
  <?php endfor; ?>
</div>

<hr>

<h2><?= htmlspecialchars($target) ?> 的新聞搜尋結果</h2>

<label for="filterInput">關鍵字篩選器：</label>
<input type="text" id="filterInput" placeholder="輸入關鍵字篩選新聞..." style="width:300px; padding:5px; margin-bottom:10px;">
<script>
  const filterInput = document.getElementById('filterInput');
  filterInput.addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const table = document.getElementById('news-table');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (let row of rows) {
      const title = row.cells[0].innerText.toLowerCase();
      const snippet = row.cells[1].innerText.toLowerCase();
      if (title.includes(filter) || snippet.includes(filter)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    }
  });
</script>


<?php if (!empty($search_results)) : ?>
  <table id="news-table">
    <thead>
      <tr>
        <th>標題</th>
        <th>摘要</th>
        <th>連結</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($search_results as $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['title']) ?></td>
          <td><?= htmlspecialchars($item['snippet']) ?></td>
          <td><a href="<?= htmlspecialchars($item['link']) ?>" target="_blank">前往</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <button class="download-btn" onclick="exportCSV()">匯出 CSV</button>
  <button class="download-btn" onclick="exportPDF()">匯出 PDF</button>
<?php else: ?>
  <p>查無結果，請確認關鍵字與 API 設定是否正確。</p>
<?php endif; ?>

</body>
</html>
