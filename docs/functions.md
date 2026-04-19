# Rujukan Fungsi - Sistem Order

## Controllers

### AuthController
| Fungsi | Tujuan | Parameter | Pulangan |
|--------|--------|-----------|----------|
| `showLogin()` | Papar halaman log masuk | - | void |
| `showRegister()` | Papar halaman daftar | - | void |
| `processLogin()` | Proses log masuk | POST: email, kata_laluan | redirect |
| `processRegister()` | Proses pendaftaran | POST: nama, email, telefon, kata_laluan | redirect |
| `logout()` | Log keluar pengguna | - | redirect |

### MenuController
| Fungsi | Tujuan | Parameter | Pulangan |
|--------|--------|-----------|----------|
| `showMenu()` | Papar menu pelanggan | - | void |
| `showMenuAdmin()` | Papar pengurusan menu admin | - | void |
| `addItem()` | Tambah item menu | POST: category_id, nama, penerangan, harga, gambar, popular | redirect |
| `editItem()` | Edit item menu | POST: id, category_id, nama, penerangan, harga, status, gambar, popular | redirect |
| `deleteItem()` | Padam item (soft delete) | GET: id | redirect |

### OrderController
| Fungsi | Tujuan | Parameter | Pulangan |
|--------|--------|-----------|----------|
| `addToCart()` | Tambah item ke cart (AJAX) | POST: item_id | JSON |
| `viewCart()` | Papar cart | - | void |
| `removeFromCart()` | Buang item dari cart (AJAX) | POST: item_id | JSON |
| `updateCart()` | Kemaskini kuantiti (AJAX) | POST: item_id, action (plus/minus) | JSON |
| `checkout()` | Proses checkout | POST: nama_pelanggan, no_meja, nota, kaedah_bayaran | redirect |
| `trackOrder()` | Jejak pesanan | GET: no | void |
| `apiOrderStatus()` | API status pesanan | GET: no | JSON |
| `getOrderHistory()` | Sejarah pesanan | - | void |

### PaymentController
| Fungsi | Tujuan | Parameter | Pulangan |
|--------|--------|-----------|----------|
| `showPaymentPage()` | Papar halaman bayaran | GET: no | void |
| `processPayment()` | Proses bayaran | POST: order_id, payment_id | redirect |

### KitchenController
| Fungsi | Tujuan | Parameter | Pulangan |
|--------|--------|-----------|----------|
| `dashboard()` | Dashboard dapur | - | void |
| `updateOrderStatus()` | Kemaskini status (AJAX) | POST: order_id, status | JSON |
| `apiGetOrders()` | API pesanan aktif | - | JSON |
| `staffCreateOrder()` | Staff buat pesanan | POST: nama_pelanggan, no_meja, nota, item_ids[], item_qtys[] | redirect |

### SalesController
| Fungsi | Tujuan | Parameter | Pulangan |
|--------|--------|-----------|----------|
| `dashboardSales()` | Dashboard jualan | - | void |
| `dailyReport()` | Laporan harian | GET: date | void |
| `monthlyReport()` | Laporan bulanan | GET: month, year | void |
| `yearlyReport()` | Laporan tahunan | GET: year | void |
| `topItems()` | Item popular | GET: period | void |
| `exportCSV()` | Eksport CSV | GET: date | CSV download |

### GroceryController
| Fungsi | Tujuan | Parameter | Pulangan |
|--------|--------|-----------|----------|
| `showGroceryDashboard()` | Dashboard belanja | - | void |
| `createList()` | Buat senarai manual | POST: tajuk, tarikh, nota, items | redirect |
| `autoGenerate()` | Auto-jana senarai | GET: days | redirect |
| `editList()` | Edit senarai | GET: id / POST: add_item | void |
| `toggleItem()` | Toggle item (AJAX) | POST: item_id, harga_sebenar | JSON |
| `completeList()` | Selesai senarai | GET: id | redirect |
| `listHistory()` | Sejarah belanja | - | void |

## Models

### UserModel
| Fungsi | Tujuan |
|--------|--------|
| `register(nama, email, telefon, kataLaluan, role)` | Daftar pengguna baru |
| `login(email, kataLaluan)` | Log masuk pengguna |
| `getUserById(id)` | Dapatkan data pengguna |
| `emailExists(email)` | Semak email sudah wujud |

### MenuModel
| Fungsi | Tujuan |
|--------|--------|
| `getAllCategories()` | Semua kategori aktif |
| `getAllItems()` | Semua item menu aktif |
| `getPopularItems()` | Item yang ditanda popular |
| `getItemById(id)` | Item berdasarkan ID |
| `addItem(...)` | Tambah item menu |
| `updateItem(id, data)` | Kemaskini item |
| `deleteItem(id)` | Padam item (soft) |

### OrderModel
| Fungsi | Tujuan |
|--------|--------|
| `createOrder(data, items)` | Buat pesanan baru (transaksi) |
| `getOrderById(id)` | Pesanan berdasarkan ID |
| `getOrderByNumber(no)` | Pesanan berdasarkan nombor |
| `getActiveOrders()` | Pesanan aktif (dapur) |
| `updateStatus(id, status)` | Kemaskini status pesanan |
| `getOrderItems(orderId)` | Item dalam pesanan |

### PaymentModel
| Fungsi | Tujuan |
|--------|--------|
| `createPayment(orderId, jumlah, kaedah)` | Buat rekod bayaran |
| `updatePaymentStatus(id, status)` | Kemaskini status bayaran |
| `getPaymentByOrder(orderId)` | Bayaran berdasarkan pesanan |

### SalesModel
| Fungsi | Tujuan |
|--------|--------|
| `getDailySales(date)` | Jualan harian |
| `getMonthlySales(month, year)` | Jualan bulanan |
| `getYearlySales(year)` | Jualan tahunan |
| `getTopSellingItems(period, limit)` | Item paling laris |
| `getSalesTrend(days)` | Trend jualan (chart) |
| `getCashFlowSummary()` | Analisis aliran tunai |

### GroceryModel
| Fungsi | Tujuan |
|--------|--------|
| `createList(data)` | Buat senarai belanja |
| `addItem(listId, data)` | Tambah item ke senarai |
| `toggleItem(itemId)` | Toggle status item |
| `autoGenerateFromSales(days, userId)` | Auto-jana dari jualan |
| `completeList(id)` | Tandak senarai selesai |
| `getListHistory()` | Sejarah senarai belanja |

## Utiliti

### Security
| Fungsi | Tujuan |
|--------|--------|
| `generateCSRFToken()` | Jana token CSRF |
| `validateCSRFToken(token)` | Sahkan token CSRF |
| `csrfField()` | HTML hidden input CSRF |
| `sanitize(input)` | Bersihkan input string |
| `sanitizeInt(input)` | Bersihkan input integer |
| `sanitizeFloat(input)` | Bersihkan input float |
| `hashPassword(password)` | Hash kata laluan |
| `verifyPassword(password, hash)` | Sahkan kata laluan |
| `requireLogin()` | Paksa log masuk |
| `requireRole(...roles)` | Paksa peranan tertentu |
| `generateOrderNumber()` | Jana nombor pesanan unik |

### Logger
| Fungsi | Tujuan |
|--------|--------|
| `error(message, context)` | Log ralat |
| `info(message, context)` | Log maklumat |
| `admin(action, details)` | Log aktiviti admin |
