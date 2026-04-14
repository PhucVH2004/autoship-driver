# Driver Route Tracking - Manual Testing Instructions

## Server Status
✅ Laravel development server is running on: http://127.0.0.1:8000

## Test URL
http://localhost:8000/admin/lo-trinh

---

## Testing Checklist

### Step 1: Verify Table Display
**URL:** http://localhost:8000/admin/lo-trinh

**Check:**
- [ ] Table shows all drivers
- [ ] Statistics columns display correct counts (Tổng đơn, Đã giao, Chưa giao)
- [ ] Status badges have correct colors:
  - Green badge for completed orders
  - Orange/yellow badge for pending orders
- [ ] "Xem lộ trình" buttons are visible and clickable
- [ ] Date filter shows today's date by default
- [ ] Table is responsive and scrollable on mobile

---

### Step 2: Verify Modal and Map Functionality
**Action:** Click "Xem lộ trình" button on any driver

**Check:**
- [ ] Modal opens smoothly without errors
- [ ] Modal title shows "Lộ trình - [Driver Name]"
- [ ] Map displays and loads OpenStreetMap tiles
- [ ] Map is interactive (can zoom and pan)
- [ ] No JavaScript errors in browser console (F12)

---

### Step 3: Verify Sidebar Information
**Location:** Right side of modal (30% width)

**Check:**
- [ ] Driver info card displays:
  - [ ] Driver name (Tên)
  - [ ] License plate (Biển số)
  - [ ] Status (Trạng thái)
- [ ] Statistics card shows:
  - [ ] Total orders (Tổng đơn)
  - [ ] Completed orders (Đã giao) in green
  - [ ] Pending orders (Chưa giao) in orange
  - [ ] Last location update timestamp

---

### Step 4: Verify Map Markers
**Location:** Map area (70% width)

**Check Delivery Point Markers:**
- [ ] Markers appear at correct customer locations
- [ ] Marker colors are correct:
  - 🟢 Green = Completed orders (trang_thai_id = 5)
  - 🔵 Blue = Current delivery (trang_thai_id = DANG_GIAO)
  - 🟠 Orange = Pending orders (all others)
- [ ] Markers are circular with white border
- [ ] Clicking a marker shows popup with:
  - [ ] Order code (ma_don)
  - [ ] Customer name
  - [ ] Customer address
  - [ ] Order status badge
  - [ ] COD amount (if applicable)

**Check Driver Location Marker (only for today's date):**
- [ ] Red circular marker appears if viewing today
- [ ] Driver marker does NOT appear when viewing past dates
- [ ] Clicking driver marker shows popup with:
  - [ ] Driver name
  - [ ] License plate
  - [ ] Last update timestamp

---

### Step 5: Verify Date Filter
**Action:** Change date in the filter and click "Xem" button

**Check:**
- [ ] Table updates with data for selected date
- [ ] Statistics reflect the selected date
- [ ] Click "Xem lộ trình" on a driver
- [ ] Modal shows correct data for selected date
- [ ] Driver location marker (red) only appears when viewing today
- [ ] Driver location marker disappears when viewing past dates

---

### Step 6: Verify Responsive Behavior
**Action:** Resize browser window to mobile size (< 768px width)

**Check:**
- [ ] Table becomes horizontally scrollable
- [ ] Modal becomes fullscreen on mobile
- [ ] Map remains functional and interactive
- [ ] Sidebar stacks below map on mobile (not side-by-side)
- [ ] All buttons remain clickable
- [ ] Text remains readable

---

### Step 7: Verify API Endpoint
**Action:** Open browser DevTools (F12) → Network tab

**Check:**
- [ ] When clicking "Xem lộ trình", a request is made to:
  `/admin/lo-trinh/tai-xe/{id}?date={date}`
- [ ] Response status is 200 OK
- [ ] Response JSON contains:
  - [ ] `driver` object with id, ho_ten, bien_so_xe, trang_thai, current_lat, current_lng, last_update
  - [ ] `orders` array with order details
  - [ ] `statistics` object with total, completed, pending
  - [ ] `show_current_location` boolean

---

## Expected Behavior Summary

### Table View
- Displays all drivers with daily statistics
- Date filter allows viewing historical data
- Clean, professional Bootstrap 5 styling

### Modal View
- Opens when clicking "Xem lộ trình"
- Shows interactive Leaflet map with delivery points
- Sidebar displays driver info and statistics
- Responsive design (fullscreen on mobile)

### Map Markers
- **Green circles**: Completed deliveries
- **Blue circles**: Current delivery in progress
- **Orange circles**: Pending deliveries
- **Red circle**: Driver's current location (today only)

### Popups
- Click any marker to see order/driver details
- Formatted with proper spacing and badges
- Shows relevant information (order code, customer, status, COD)

---

## Common Issues to Check

### If map doesn't display:
- Check browser console for Leaflet errors
- Verify internet connection (Leaflet loads from CDN)
- Ensure modal has fully opened before map initializes

### If markers don't appear:
- Check that orders have valid latitude/longitude in database
- Verify API response contains order data
- Check browser console for JavaScript errors

### If driver location doesn't show:
- Verify you're viewing today's date
- Check that driver has current_lat and current_lng in database
- Ensure `show_current_location` is true in API response

---

## Files Verified

✅ Controller: `app/Http/Controllers/Admin/LoTrinhController.php`
✅ View: `resources/views/admin/lo_trinh/index.blade.php`
✅ Routes: `routes/web.php` (lines 133-134)
✅ JavaScript: Embedded in view file (lines 186-318)

---

## Next Steps

After completing all tests above:
1. If all tests pass, run: `git add -A && git commit -m "test: verify all features of driver route tracking redesign"`
2. If any issues found, document them and fix before committing
