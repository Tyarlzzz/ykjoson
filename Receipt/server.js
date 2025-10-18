const express = require('express');
const escpos = require('escpos');
escpos.Serial = require('escpos-serialport');
const cors = require('cors');
const mysql = require('mysql2/promise');

const app = express();
app.use(express.json());
app.use(cors());

// Replace 'COM3' with your actual Bluetooth COM port
const COM_PORT = 'COM9';
const BAUD_RATE = 9600;

// Database configuration - UPDATE WITH YOUR CREDENTIALS
const dbConfig = {
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'ykjoson'
};

// Helper function to pad text for 48mm printable width (32 characters)
function padLine(left, right, totalWidth = 32) {
  const spaces = totalWidth - left.length - right.length;
  return left + ' '.repeat(Math.max(0, spaces)) + right;
}

// Endpoint to print receipt with order data
app.post('/print-receipt', async (req, res) => {
  const { order_id } = req.body;

  if (!order_id) {
    return res.status(400).json({ 
      success: false, 
      message: 'Missing order_id' 
    });
  }

  try {
    // Connect to database
    const connection = await mysql.createConnection(dbConfig);

    // Fetch order details with customer info
    const [orderRows] = await connection.execute(`
      SELECT o.*, 
             c.fullname, c.phone_number, c.address
      FROM orders o
      JOIN customer c ON o.customer_id = c.customer_id
      WHERE o.order_id = ?
    `, [order_id]);

    if (orderRows.length === 0) {
      await connection.end();
      return res.status(404).json({ 
        success: false, 
        message: 'Order not found' 
      });
    }

    const order = orderRows[0];

    // Fetch order items
    const [itemRows] = await connection.execute(`
      SELECT loi.*, pc.product_name, pc.category
      FROM laundry_ordered_items loi
      LEFT JOIN \`product codes\` pc ON loi.product_code_id = pc.code_id
      WHERE loi.order_id = ?
      ORDER BY pc.category, pc.product_name
    `, [order_id]);

    await connection.end();

    // Calculate totals
    let totalQty = 0;
    let totalWeight = 0;
    let totalAmount = 0;
    let clothesWeight = 0;
    let comforterWeight = 0;

    itemRows.forEach(item => {
      totalQty += parseInt(item.quantity || 0);
      totalWeight += parseFloat(item.weight_kg || 0);
      totalAmount += parseFloat(item.total || 0);

      if (item.category === 'Clothing') {
        clothesWeight += parseFloat(item.weight_kg || 0);
      } else if (item.product_name === 'Comforter' || item.product_name === 'Curtains') {
        comforterWeight += parseFloat(item.weight_kg || 0);
      }
    });

    // Prepare printer
    const device = new escpos.Serial(COM_PORT, {
      baudRate: BAUD_RATE,
      autoOpen: false
    });
    const printer = new escpos.Printer(device);

    device.open(function(error) {
      if (error) {
        console.error('Error opening device:', error);
        return res.status(500).json({ 
          success: false, 
          message: error.message 
        });
      }

      try {
        // Print header
        printer
          .font('a')
          .align('ct')
          .style('bu')
          .size(1, 1)
          .text('YK Joson')
          .style('normal')
          .size(0, 0)
          .text('Laundry and Gasul Services')
          .feed(1)
          .text('================================')
          .feed(1);

        // Date - Left aligned
        const orderDate = new Date(order.order_date);
        printer
          .align('lt')
          .text(`Date: ${orderDate.toLocaleDateString()}`)
          .text('--------------------------------');

        // Customer Info
        const customerName = order.fullname;
        const phone = order.phone_number || 'N/A';
        const address = order.address || 'N/A';

        printer
          .text(`Name: ${customerName}`)
          .text(`Phone: ${phone}`)
          .text(`Address: ${address}`)
          .text('--------------------------------');

        // Rushed order checkbox
        const rushedCheck = order.is_rushed ? '[X]' : '[ ]';
        printer
          .text(`${rushedCheck} Rushed Order`)
          .feed(1);

        // Rider name (hardcoded for now)
        const riderName = 'Rommel Santos';
        printer
          .text(`Rider: ${riderName}`)
          .text('--------------------------------')
          .feed(1);

        // Items header
        printer
          .text('Item Name                    Qty')
          .text('--------------------------------');

        // Print items grouped by category
        itemRows.forEach(item => {
          const name = (item.product_name || 'Item').substring(0, 25);
          const qty = String(item.quantity || 0);
          printer.text(padLine(name, qty));
        });

        printer.text('--------------------------------');

        // Summary
        printer
          .text(padLine('Total Qty:', String(totalQty)))
          .text(padLine('Clothes Weight:', `${clothesWeight.toFixed(2)} kg`))
          .text(padLine('Comforter Weight:', `${comforterWeight.toFixed(2)} kg`))
          .text(padLine('Total Weight:', `${totalWeight.toFixed(2)} kg`))
          .text('================================');

        // TOTAL (large)
        printer
          .align('ct')
          .size(1, 1)
          .text(`TOTAL: P${totalAmount.toFixed(2)}`)
          .size(0, 0)
          .text('================================')
          .feed(1);

        // Note if exists
        if (order.note) {
          printer
            .align('lt')
            .text('Note:')
            .text(order.note)
            .feed(1);
        }

        // Footer
        printer
          .align('ct')
          .text('Thank You!')
          .text('Please Come Again')
          .feed(1)
          .text('================================')
          .feed(3)
          .cut()
          .close(() => {
            res.json({ 
              success: true, 
              message: 'Receipt printed successfully!' 
            });
          });

      } catch (err) {
        console.error('Printing error:', err);
        res.status(500).json({ 
          success: false, 
          message: err.message 
        });
      }
    });

  } catch (error) {
    console.error('Database error:', error);
    res.status(500).json({ 
      success: false, 
      message: 'Database error: ' + error.message 
    });
  }
});

// Test endpoint
app.post('/print-test', (req, res) => {
  const device = new escpos.Serial(COM_PORT, {
    baudRate: BAUD_RATE,
    autoOpen: false
  });
  const printer = new escpos.Printer(device);

  device.open(function(error) {
    if (error) {
      console.error('Error opening device:', error);
      return res.status(500).json({ 
        success: false, 
        error: error.message 
      });
    }

    try {
      printer
        .font('a')
        .align('ct')
        .size(1, 1)
        .text('TEST PRINT')
        .size(0, 0)
        .text('YK Joson Laundry')
        .text('58mm Paper (48mm Printable)')
        .feed(2)
        .text('Printer is working!')
        .feed(3)
        .cut()
        .close(() => {
          res.json({ 
            success: true, 
            message: 'Test receipt printed successfully!' 
          });
        });
    } catch (err) {
      console.error('Printing error:', err);
      res.status(500).json({ 
        success: false, 
        error: err.message 
      });
    }
  });
});

app.get('/test', (req, res) => {
  res.json({ 
    status: 'Server is running', 
    port: COM_PORT,
    paperSize: '58mm (48mm printable)'
  });
});

const PORT = 3001;
app.listen(PORT, () => {
  console.log(`Print server running on http://localhost:${PORT}`);
  console.log(`Using COM port: ${COM_PORT}`);
});
