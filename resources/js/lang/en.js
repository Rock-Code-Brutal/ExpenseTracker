export default {
  // Navigation & Header
  app_title: "💰 Expense Tracker",
  dashboard: "📊 Dashboard", 
  transactions: "💳 Transactions",
  categories: "📂 Categories",
  
  // Dashboard
  total_balance: "Total Balance",
  monthly_income: "This Month's Income",
  monthly_expense: "This Month's Expense",
  recent_transactions: "Recent Transactions", 
  monthly_income_vs_expense: "Monthly Income vs Expense",
  no_recent_transactions: "No Recent Transactions. Add Your First Transaction!",
  loading: "Loading...",
  
  // Transactions
  add_transaction: "+ Add Transaction",
  edit_transaction: "Edit Transaction", 
  export_csv: "📊 Export CSV",
  transaction_type: "Type",
  category: "Category",
  amount: "Amount",
  date: "Date",
  description: "Description (Optional)",
  description_placeholder: "Transaction description",
  income: "Income",
  expense: "Expense", 
  select_type: "-- Select Type --",
  select_category: "-- Select Category --",
  cancel: "Cancel",
  save_transaction: "Save Transaction",
  update_transaction: "Update Transaction",
  saving: "Saving...",
  
  // Filters
  filter_search: "🔍 Filter & Search",
  show_filters: "Show Filters",
  hide_filters: "Hide Filters", 
  search: "Search",
  search_placeholder: "Search by description or category...",
  all_types: "All Types",
  all_categories: "All Categories",
  from_date: "From Date",
  to_date: "To Date",
  clear_filters: "🗑️ Clear All Filters",
  
  // Transaction List
  no_transactions: "No transactions yet. Add your first transaction!",
  no_description: "No description",
  edit: "Edit",
  delete: "Delete", 
  delete_confirmation: "Delete this transaction?",
  
  // Categories
  add_category: "+ Add Category",
  category_name: "Category Name",
  category_name_placeholder: "e.g.: Salary, Food, etc.",
  color: "Color",
  income_categories: "💰 Income Categories",
  expense_categories: "💸 Expense Categories",
  income_categories_desc: "Your income sources",
  expense_categories_desc: "Your expense categories",
  no_categories: "No Categories Yet, Please Add Some First!",
  loading_categories: "Loading Categories...",
  save_category: "Save Category",
  delete_category_confirmation: (name) => `Delete category "${name}"? All transactions with this category will also be deleted!`,
  
  // Theme
  switch_to_light: "Switch to Light Mode",
  switch_to_dark: "Switch to Dark Mode",
  
  // Pagination
  load_more: "Load More",
  showing: "Showing",
  of: "of",
  
  // Messages
  failed_to_save: "Failed to save transaction",
  failed_to_update: "Failed to update transaction",
  failed_to_delete: "Failed to delete transaction", 
  failed_to_load: "Failed to load data",
  no_data_to_export: "No data to export",
}
