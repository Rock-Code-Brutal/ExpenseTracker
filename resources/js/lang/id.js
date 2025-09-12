export default {
  // Navigation & Header
  app_title: "💰 Tracker Pengeluaran",
  dashboard: "📊 Dashboard",
  transactions: "💳 Transaksi",
  categories: "📂 Kategori",
  
  // Dashboard
  total_balance: "Total Saldo",
  monthly_income: "Pemasukan Bulan Ini",
  monthly_expense: "Pengeluaran Bulan Ini", 
  recent_transactions: "Transaksi Terakhir",
  monthly_income_vs_expense: "Pemasukan vs Pengeluaran Bulanan",
  no_recent_transactions: "Tidak Ada Transaksi Terakhir. Tambah Transaksi Pertamamu!",
  loading: "Memuat...",
  
  // Transactions
  add_transaction: "+ Tambah Transaksi",
  edit_transaction: "Edit Transaksi",
  export_csv: "📊 Export CSV",
  transaction_type: "Tipe",
  category: "Kategori",
  amount: "Jumlah",
  date: "Tanggal",
  description: "Deskripsi (Opsional)",
  description_placeholder: "Deskripsi transaksi",
  income: "Pemasukan",
  expense: "Pengeluaran",
  select_type: "-- Pilih Tipe --",
  select_category: "-- Pilih Kategori --",
  cancel: "Batal",
  save_transaction: "Simpan Transaksi",
  update_transaction: "Update Transaksi",
  saving: "Menyimpan...",
  
  // Filters
  filter_search: "🔍 Filter & Search",
  show_filters: "Tampilkan Filter",
  hide_filters: "Sembunyikan Filter",
  search: "Cari",
  search_placeholder: "Cari berdasarkan deskripsi atau kategori...",
  all_types: "Semua",
  all_categories: "Semua Kategori",
  from_date: "Dari Tanggal",
  to_date: "Sampai Tanggal",
  clear_filters: "🗑️ Hapus Semua Filter",
  
  // Transaction List
  no_transactions: "Belum ada transaksi. Tambah transaksi pertamamu!",
  no_description: "Tidak ada deskripsi",
  edit: "Edit",
  delete: "Hapus",
  delete_confirmation: "Hapus transaksi ini?",
  
  // Categories
  add_category: "+ Tambah Kategori",
  category_name: "Nama Kategori",
  category_name_placeholder: "Contoh: Gaji, Makan, dll",
  color: "Warna",
  income_categories: "💰 Kategori Pemasukan",
  expense_categories: "💸 Kategori Pengeluaran",
  income_categories_desc: "Sumber pemasukan Anda",
  expense_categories_desc: "Kategori pengeluaran Anda",
  no_categories: "Belum Ada Kategori, Silahkan Tambah Dulu!",
  loading_categories: "Memuat Kategori...",
  save_category: "Simpan Kategori",
  delete_category_confirmation: (name) => `Hapus kategori "${name}"? Semua transaksi dengan kategori ini akan terhapus juga!`,
  
  // Theme
  switch_to_light: "Beralih ke Mode Terang",
  switch_to_dark: "Beralih ke Mode Gelap",
  
  // Pagination
  load_more: "Muat Lebih Banyak",
  showing: "Menampilkan",
  of: "dari",
  
  // Messages
  failed_to_save: "Gagal menyimpan transaksi",
  failed_to_update: "Gagal mengupdate transaksi", 
  failed_to_delete: "Gagal menghapus transaksi",
  failed_to_load: "Gagal memuat data",
  no_data_to_export: "Tidak ada data untuk diexport",
}
