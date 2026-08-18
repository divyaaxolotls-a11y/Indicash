import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:webview_flutter_android/webview_flutter_android.dart';
import 'package:webview_flutter_wkwebview/webview_flutter_wkwebview.dart';
import 'package:file_picker/file_picker.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:path_provider/path_provider.dart';
import 'package:flutter_cache_manager/flutter_cache_manager.dart';
import 'package:open_filex/open_filex.dart';
import 'package:device_info_plus/device_info_plus.dart';
import 'package:flutter/services.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:image_picker/image_picker.dart';
import 'package:url_launcher/url_launcher.dart';

import 'no_internet_connection_widget.dart';

class FullFeatureWebView extends StatefulWidget {
  final String initialUrl;
  final Map<String, String>? headers;

  const FullFeatureWebView({super.key, required this.initialUrl, this.headers});

  @override
  State<FullFeatureWebView> createState() => _FullFeatureWebViewState();
}

class _FullFeatureWebViewState extends State<FullFeatureWebView> {
  WebViewController? _controller;
  bool _isLoading = true;
  double _progress = 0;
  bool _isOffline = false;
  bool _isFileUploading = false;
  bool _hasError = false;
  String? _errorMessage;
  late StreamSubscription<List<ConnectivityResult>> _connectivitySubscription;

  @override
  void initState() {
    super.initState();
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);

    // Initialize connectivity check
    _initConnectivity();

    // Start listening to connectivity changes
    _connectivitySubscription = Connectivity().onConnectivityChanged.listen((
      List<ConnectivityResult> results,
    ) {
      // Check if any of the results indicate we're online
      final isOnline = results.any(
        (result) => result != ConnectivityResult.none,
      );

      setState(() {
        _isOffline = !isOnline;
        if (!_isOffline && _hasError) {
          // If we're back online and had an error, try reloading
          _initializeWebViewController();
        }
      });
    });
  }

  Future<void> _initConnectivity() async {
    try {
      final connectivityResult = await Connectivity().checkConnectivity();
      setState(() {
        _isOffline = connectivityResult.every(
          (result) => result == ConnectivityResult.none,
        );
      });

      if (!_isOffline) {
        _initializeWebViewController();
      }
    } catch (e) {
      debugPrint('Connectivity check error: $e');
      // If connectivity check fails, assume offline and try to initialize anyway
      setState(() {
        _isOffline = true;
      });
      _initializeWebViewController();
    }
  }

  @override
  void dispose() {
    _connectivitySubscription.cancel();
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);
    super.dispose();
  }

  Future<void> _initializeWebViewController() async {
    try {
      late final PlatformWebViewControllerCreationParams params;
      if (WebViewPlatform.instance is WebKitWebViewPlatform) {
        params = WebKitWebViewControllerCreationParams(
          allowsInlineMediaPlayback: true,
          mediaTypesRequiringUserAction: const <PlaybackMediaTypes>{},
        );
      } else {
        params = const PlatformWebViewControllerCreationParams();
      }

      final WebViewController controller =
          WebViewController.fromPlatformCreationParams(params);

      // Set navigation delegate first
      controller.setNavigationDelegate(
        NavigationDelegate(
          onNavigationRequest: (NavigationRequest request) {
            // Handle navigation requests
            if (request.url.startsWith('tel:')) {
              _launchPhoneDialer(request.url);
              return NavigationDecision.prevent;
            }
            return NavigationDecision.navigate;
          },
          onPageStarted: (String url) {
            setState(() {
              _isLoading = true;
              _hasError = false;
              _errorMessage = null;
            });
          },
          onPageFinished: (String url) {
            setState(() => _isLoading = false);
            _injectCustomCSS();
            _enableFullFunctionality();
            _setupDownloadListener();
          },
          onProgress: (int progress) {
            setState(() {
              _progress = progress / 100;
              _isLoading = progress < 100;
              if (progress == 100) {
                _hasError = false;
                _errorMessage = null;
              }
            });
          },
          onWebResourceError: (WebResourceError error) {
            debugPrint(
              'WebView error: ${error.errorCode} - ${error.description}',
            );
            debugPrint('Error type: ${error.errorType}');
            if (error.errorCode != -1) {
              setState(() {
                _hasError = true;
                _errorMessage =
                    'Error ${error.errorCode}: ${error.description}';
                _isLoading = false;
              });
            } else {
              debugPrint(
                'Resource loading failed (non-critical): ${error.url}',
              );
              setState(() {
                _isLoading = false;
              });
            }
          },
          onUrlChange: (UrlChange change) {
            debugPrint('URL changed to: ${change.url}');
            // Special handling for blob URLs (used by PDF generation)
            if (change.url?.startsWith('blob:') ?? false) {
              _handleBlobUrl(change.url!);
            }
          },
          onHttpAuthRequest: (HttpAuthRequest request) {
            // Handle HTTP authentication requests
            debugPrint('HTTP auth required for ${request.host}');
            // You could show a dialog to enter credentials here
            // request.proceed('username', 'password');
            // request.cancel();
          },
        ),
      );

      // Set other controller properties
      controller
        ..setJavaScriptMode(JavaScriptMode.unrestricted)
        ..setBackgroundColor(Colors.transparent)
        ..enableZoom(false)
        ..addJavaScriptChannel(
          'Flutter',
          onMessageReceived: (message) {
            _handleJavaScriptMessage(message.message);
          },
        );

      // Load the initial URL
      if (widget.headers != null) {
        await controller.loadRequest(Uri.parse(widget.initialUrl),
            headers: widget.headers!);
      } else {
        await controller.loadRequest(Uri.parse(widget.initialUrl));
      }

      // Platform-specific configurations
      if (controller.platform is AndroidWebViewController) {
        final androidController =
            controller.platform as AndroidWebViewController;
        await androidController.setOnShowFileSelector(_androidFilePicker);
        await androidController.setJavaScriptMode(JavaScriptMode.unrestricted);

        // Add these important settings:
        await androidController.setAllowFileAccess(true);
        await androidController.setAllowContentAccess(true);
        await androidController.setMediaPlaybackRequiresUserGesture(false);
      } else if (controller.platform is WebKitWebViewController) {
        final webKitController = controller.platform as WebKitWebViewController;
        await webKitController.setAllowsBackForwardNavigationGestures(true);
      }

      setState(() => _controller = controller);
    } catch (e) {
      debugPrint('Error initializing WebViewController: $e');
      setState(() {
        _hasError = true;
        _errorMessage = 'Failed to initialize web view: ${e.toString()}';
        _isLoading = false;
      });
    }
  }

  void _savePdf(Uint8List bytes, String filename) async {
    try {
      final directory = await getDownloadsDirectory();
      if (directory == null) {
        throw Exception('Downloads directory not available');
      }

      final file = File('${directory.path}/$filename');
      await file.writeAsBytes(bytes, flush: true);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('PDF saved successfully'),
            action: SnackBarAction(
              label: 'Open',
              onPressed: () => OpenFilex.open(file.path),
            ),
          ),
        );
      }
    } catch (e) {
      debugPrint('Save PDF error: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to save PDF: ${e.toString()}')),
        );
      }
    }
  }

  Future<bool> _checkStoragePermission() async {
    if (Platform.isAndroid) {
      final status = await Permission.storage.status;
      if (!status.isGranted) {
        final result = await Permission.storage.request();
        return result.isGranted;
      }
      return true;
    }
    return true; // iOS doesn't need storage permission for downloads directory
  }

  void _handleBlobUrl(String blobUrl) async {
    try {
      // This approach works around WebView blob URL limitations
      final js = """
      (function() {
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = '$blobUrl';
        iframe.onload = function() {
          const canvas = document.createElement('canvas');
          const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
          canvas.width = iframeDoc.width;
          canvas.height = iframeDoc.height;
          
          const ctx = canvas.getContext('2d');
          ctx.drawWindow(iframe.contentWindow, 0, 0, canvas.width, canvas.height, 'white');
          
          canvas.toBlob(function(blob) {
            const reader = new FileReader();
            reader.onloadend = function() {
              window.Flutter.postMessage(JSON.stringify({
                type: 'pdfData',
                data: reader.result,
                filename: 'document.pdf'
              }));
            };
            reader.readAsDataURL(blob);
          }, 'application/pdf');
        };
        document.body.appendChild(iframe);
      })();
    """;

      await _controller?.runJavaScript(js);
    } catch (e) {
      debugPrint('Error handling blob URL: $e');
      // Fallback to alternative approach
      _handlePdfFallback();
    }
  }

  Future<void> _handlePdfFallback() async {
    try {
      // Directly ask the web page to generate PDF as base64
      final js = """
      (function() {
        const element = document.querySelector('.invoice-box');
        if (!element) {
          window.Flutter.postMessage(JSON.stringify({
            type: 'pdfError',
            message: 'Invoice element not found'
          }));
          return;
        }
        
        html2pdf()
          .from(element)
          .outputPdf('datauristring')
          .then(function(pdfData) {
            window.Flutter.postMessage(JSON.stringify({
              type: 'pdfData',
              data: pdfData,
              filename: 'invoice.pdf'
            }));
          })
          .catch(function(error) {
            window.Flutter.postMessage(JSON.stringify({
              type: 'pdfError',
              message: error.message
            }));
          });
      })();
    """;

      await _controller?.runJavaScript(js);
    } catch (e) {
      debugPrint('PDF fallback error: $e');
    }
  }

  Future<void> _refreshPage() async {
    if (_controller != null) {
      await _controller!.reload();
    } else {
      await _initializeWebViewController();
    }
  }

  Future<void> _launchPhoneDialer(String phoneUrl) async {
    try {
      if (await canLaunchUrl(Uri.parse(phoneUrl))) {
        await launchUrl(Uri.parse(phoneUrl));
      } else {
        debugPrint('Could not launch $phoneUrl');
        if (mounted) {
          ScaffoldMessenger.of(
            context,
          ).showSnackBar(SnackBar(content: Text('Cannot make phone call')));
        }
      }
    } catch (e) {
      debugPrint('Error launching phone dialer: $e');
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Error initiating phone call')));
      }
    }
  }

  Future<List<String>> _androidFilePicker(FileSelectorParams params) async {
    debugPrint(
      "Android file picker triggered with accept: ${params.acceptTypes}",
    );
    setState(() => _isFileUploading = true);

    try {
      final isImageRequest = params.acceptTypes.any(
            (type) =>
                type.contains('image/*') ||
                type.contains('.jpg') ||
                type.contains('.jpeg') ||
                type.contains('.png'),
          ) ??
          false;

      if (isImageRequest) {
        final choice = await showDialog<String>(
          context: context,
          builder: (context) => Theme(
            data: Theme.of(context).copyWith(
              dialogTheme: DialogThemeData(
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16.0),
                ),
              ),
            ),
            child: AlertDialog(
              title: Center(
                child: Align(
                  alignment: AlignmentDirectional.centerStart,
                  child: Text(
                    'Choose',
                    style: TextStyle(
                      color: Colors.black87,
                      fontSize: 25,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: [
                      Column(
                        children: [
                          IconButton(
                            icon: Icon(
                              Icons.camera_alt,
                              size: 60,
                              color: Colors.black,
                            ),
                            color: Colors.blue,
                            onPressed: () => Navigator.pop(context, 'camera'),
                          ),
                          SizedBox(height: 4),
                          Text('Camera', style: TextStyle(fontSize: 14)),
                        ],
                      ),
                      Column(
                        children: [
                          IconButton(
                            icon: Icon(
                              Icons.photo_library,
                              size: 60,
                              color: Colors.black,
                            ),
                            color: Colors.blue,
                            onPressed: () => Navigator.pop(context, 'gallery'),
                          ),
                          SizedBox(height: 4),
                          Text('Gallery', style: TextStyle(fontSize: 16)),
                        ],
                      ),
                    ],
                  ),
                  SizedBox(height: 16),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context, 'cancel'),
                  style: TextButton.styleFrom(
                    minimumSize: Size(double.infinity, 48),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.only(
                        bottomLeft: Radius.circular(16),
                        bottomRight: Radius.circular(16),
                      ),
                    ),
                  ),
                  child: Align(
                    alignment: AlignmentDirectional.centerEnd,
                    child: Text(
                      'CANCEL',
                      style: TextStyle(
                        color: Colors.red,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        );

        if (choice == 'cancel') return [];
        if (choice == 'camera') {
          return await _takePhotoWithCamera();
        }
      }

      final isPermissionGranted = await _requestStoragePermission();
      if (!isPermissionGranted) {
        debugPrint("Storage permission not granted");
        return [];
      }

      FileType fileType = FileType.any;
      List<String>? allowedExtensions;

      if (params.acceptTypes.isNotEmpty ?? false) {
        final acceptTypes = params.acceptTypes;
        if (acceptTypes.any((type) => type.contains('image/*'))) {
          fileType = FileType.image;
        } else if (acceptTypes.any((type) => type.contains('video/*'))) {
          fileType = FileType.video;
        } else if (acceptTypes.any((type) => type.contains('audio/*'))) {
          fileType = FileType.audio;
        } else {
          allowedExtensions = acceptTypes
              .where((type) => type.startsWith('.'))
              .map((type) => type.substring(1))
              .toList();
        }
      }

      FilePickerResult? result = await FilePicker.platform.pickFiles(
        type: fileType,
        allowedExtensions: allowedExtensions,
        allowMultiple: params.acceptTypes.length > 1,
      );

      if (result == null || result.files.isEmpty) return [];
      if (Platform.isAndroid) {
        final tempDir = await getTemporaryDirectory();
        final contentUris = <String>[];

        for (final file in result.files) {
          if (file.path != null) {
            final originalFile = File(file.path!);
            final tempFile = await originalFile.copy(
              '${tempDir.path}/${DateTime.now().millisecondsSinceEpoch}_${file.name}',
            );
            contentUris.add(tempFile.uri.toString());
          }
        }
        return contentUris;
      }

      return result.files
          .where((file) => file.path != null)
          .map((file) => file.path!)
          .toList();
    } catch (e) {
      debugPrint("Error in file picker: $e");
      return [];
    } finally {
      setState(() => _isFileUploading = false);
    }
  }

  Future<List<String>> _takePhotoWithCamera() async {
    try {
      // Request camera permission
      final cameraStatus = await Permission.camera.request();
      if (!cameraStatus.isGranted) {
        throw Exception('Camera permission not granted');
      }

      // Use image_picker package for camera functionality
      final picker = ImagePicker();
      final XFile? photo = await picker.pickImage(
        source: ImageSource.camera,
        maxWidth: 1920,
        maxHeight: 1080,
        imageQuality: 90,
      );

      if (photo == null) return [];

      // Save the image to temporary directory
      final tempDir = await getTemporaryDirectory();
      final fileName = 'camera_${DateTime.now().millisecondsSinceEpoch}.jpg';
      final savedFile = File('${tempDir.path}/$fileName');
      await savedFile.writeAsBytes(await photo.readAsBytes());

      return [savedFile.uri.toString()];
    } catch (e) {
      debugPrint("Camera error: $e");
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(
            SnackBar(content: Text('Camera error: ${e.toString()}')));
      }
      return [];
    }
  }

  Future<bool> _requestStoragePermission() async {
    if (Platform.isAndroid) {
      final deviceInfo = await DeviceInfoPlugin().androidInfo;
      final androidVersion = deviceInfo.version.sdkInt;

      Permission permission;
      if (androidVersion >= 33) {
        permission = Permission.photos;
      } else {
        permission = Permission.storage;
      }

      final status = await permission.status;
      if (status.isGranted) return true;
      if (status.isPermanentlyDenied) {
        _showPermissionSettingsDialog();
        return false;
      }

      final result = await permission.request();
      return result.isGranted;
    }
    return true;
  }

  void _showPermissionSettingsDialog() {
    showDialog(
      context: context,
      builder: (BuildContext context) => AlertDialog(
        title: const Text('Permission Required'),
        content: const Text(
          'Storage permission is required to upload files. Please enable it in app settings.',
        ),
        actions: <Widget>[
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              openAppSettings();
            },
            child: const Text('Open Settings'),
          ),
        ],
      ),
    );
  }

  void _handleJavaScriptMessage(String message) {
    try {
      final json = jsonDecode(message);

      if (json['type'] == 'pdfData') {
        final data = json['data'];
        if (data.startsWith('data:')) {
          final base64Data = data.split(',').last;
          final bytes = base64.decode(base64Data);
          _savePdf(bytes, json['filename'] ?? 'document.pdf');
        }
      } else if (json['type'] == 'pdfError') {
        debugPrint('PDF generation error: ${json['message']}');
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('PDF Error: ${json['message']}')),
          );
        }
      } else if (message.startsWith('download:')) {
        final url = message.substring('download:'.length);
        _handleDownload(url);
      } else if (message.startsWith('phone:')) {
        final phoneUrl = message.substring('phone:'.length);
        _launchPhoneDialer(phoneUrl);
      }
    } catch (e) {
      debugPrint('Error handling JS message: $e');
    }
  }

  Future<void> _iosFilePicker() async {
    setState(() => _isFileUploading = true);
    try {
      final choice = await showDialog<String>(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Select Source'),
          content: const Text('Choose how to select an image'),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context, 'camera'),
              child: const Text('Camera'),
            ),
            TextButton(
              onPressed: () => Navigator.pop(context, 'gallery'),
              child: const Text('Gallery'),
            ),
            TextButton(
              onPressed: () => Navigator.pop(context, 'cancel'),
              child: const Text('Cancel'),
            ),
          ],
        ),
      );

      if (choice == 'cancel') return;

      if (choice == 'camera') {
        final picker = ImagePicker();
        final XFile? photo = await picker.pickImage(
          source: ImageSource.camera,
          maxWidth: 1920,
          maxHeight: 1080,
          imageQuality: 90,
        );

        if (photo != null) {
          final bytes = await photo.readAsBytes();
          final base64Image = base64Encode(bytes);
          await _controller!.runJavaScript('''
          (function() {
            const input = document.querySelector('input[type="file"]');
            if (input) {
              const file = new File([""], "${photo.name}", {
                type: "image/jpeg",
                lastModified: ${DateTime.now().millisecondsSinceEpoch}
              });
              Object.defineProperty(file, 'size', { value: ${bytes.length} });
              
              const dataTransfer = new DataTransfer();
              dataTransfer.items.add(file);
              input.files = dataTransfer.files;
              
              const event = new Event('change', { bubbles: true });
              input.dispatchEvent(event);
            }
          })();
        ''');
        }
        return;
      }

      // Original gallery picker logic
      final result = await FilePicker.platform.pickFiles(
        allowMultiple: true,
        type: FileType.any,
      );

      if (result != null && result.files.isNotEmpty) {
        final fileListJS = result.files.map((file) {
          final fileName = file.name;
          final mimeType = file.extension ?? 'application/octet-stream';
          return '''
          {
            name: "$fileName",
            type: "$mimeType",
            size: ${file.size ?? 0},
            lastModified: ${DateTime.now().millisecondsSinceEpoch}
          }
        ''';
        }).join(',');

        await _controller!.runJavaScript('''
        (function() {
          const input = document.querySelector('input[type="file"]');
          if (input) {
            const files = [$fileListJS];
            const dataTransfer = new DataTransfer();
            
            files.forEach(fileInfo => {
              const file = new File([""], fileInfo.name, {
                type: fileInfo.type,
                lastModified: fileInfo.lastModified
              });
              Object.defineProperty(file, 'size', { value: fileInfo.size });
              dataTransfer.items.add(file);
            });
            
            input.files = dataTransfer.files;
            const event = new Event('change', { bubbles: true });
            input.dispatchEvent(event);
          }
        })();
      ''');
      }
    } catch (e) {
      debugPrint('iOS file picker error: $e');
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(const SnackBar(content: Text('Failed to select files')));
      }
    } finally {
      setState(() => _isFileUploading = false);
    }
  }

  Future<void> _setupDownloadListener() async {
    if (_controller == null) return;

    try {
      await _controller!.runJavaScript('''
      (function() {
        // Intercept all clicks
        document.addEventListener('click', function(e) {
          let target = e.target;
          while (target && target.nodeName !== 'A') {
            target = target.parentElement;
          }
          
          if (target && target.href) {
            const href = target.href.toLowerCase();
            // Check if this is a download link
            if (href.endsWith('.pdf') || 
                href.endsWith('.doc') || 
                href.endsWith('.docx') || 
                href.endsWith('.xls') || 
                href.endsWith('.xlsx') || 
                href.endsWith('.zip') || 
                href.endsWith('.rar') || 
                target.hasAttribute('download')) {
              e.preventDefault();
              window.Flutter.postMessage('download:' + target.href);
              return false;
            }
          }
          return true;
        });
        
        // Also intercept window.open for downloads
        const originalWindowOpen = window.open;
        window.open = function(url, target, features) {
          if (url && (url.toLowerCase().endsWith('.pdf') || 
                      url.toLowerCase().endsWith('.doc') || 
                      url.toLowerCase().endsWith('.docx') || 
                      url.toLowerCase().endsWith('.xls') || 
                      url.toLowerCase().endsWith('.xlsx') || 
                      url.toLowerCase().endsWith('.zip') || 
                      url.toLowerCase().endsWith('.rar'))) {
            window.Flutter.postMessage('download:' + url);
            return null;
          }
          return originalWindowOpen(url, target, features);
        };
      })();
    ''');
    } catch (e) {
      debugPrint('Error setting up download listener: $e');
    }
  }

  Future<void> _handleDownload(String url) async {
    try {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Downloading: ${Uri.parse(url).pathSegments.last}'),
          ),
        );
      }

      final file = await DefaultCacheManager().getSingleFile(url);
      final savedDir = await getDownloadsDirectory();
      final fileName = Uri.parse(url).pathSegments.last;
      final savedFile = File('${savedDir?.path}/$fileName');

      await file.copy(savedFile.path);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Download complete: $fileName'),
            action: SnackBarAction(
              label: 'Open',
              onPressed: () => OpenFilex.open(savedFile.path),
            ),
          ),
        );
      }
    } catch (e) {
      debugPrint('Download error: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Download failed: ${e.toString()}')),
        );
      }
    }
  }

  Future<void> _injectCustomCSS() async {
    if (_controller == null) return;

    try {
      await _controller!.runJavaScript('''
      (function() {
        // Improve font rendering
        const style = document.createElement('style');
        style.textContent = `
          * {
            text-rendering: optimizeLegibility !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
          }
          
          // Fix for React apps that might have overflow issues
          body, html {
            overflow: auto !important;
            overscroll-behavior: none !important;
          }
          
          // Ensure inputs are visible
          input, textarea, select, button {
            font-size: initial !important;
            min-height: initial !important;
          }
        `;
        document.head.appendChild(style);
      })();
    ''');
    } catch (e) {
      debugPrint('Error injecting custom CSS: $e');
    }
  }

  Future<void> _handlePdfDownload(String pdfData, String fileName) async {
    try {
      // Decode the base64 PDF data
      final bytes = base64.decode(pdfData.split(',')[1]);

      // Get the downloads directory
      final directory = await getDownloadsDirectory();
      if (directory == null) {
        throw Exception('Could not access downloads directory');
      }

      // Save the file
      final file = File('${directory.path}/$fileName');
      await file.writeAsBytes(bytes);

      // Show success message
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('PDF saved: $fileName'),
            action: SnackBarAction(
              label: 'Open',
              onPressed: () => OpenFilex.open(file.path),
            ),
          ),
        );
      }
    } catch (e) {
      debugPrint('PDF download error: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to save PDF: ${e.toString()}')),
        );
      }
    }
  }

  Future<void> _enableFullFunctionality() async {
    if (_controller == null) return;

    try {
      await _controller!.runJavaScript('''
  (function() {
    // Intercept clicks on phone numbers
    document.addEventListener('click', function(e) {
      let target = e.target;
      while (target && target.nodeName !== 'A') {
        target = target.parentElement;
      }
      
      if (target && target.href && target.href.startsWith('tel:')) {
        e.preventDefault();
        window.Flutter.postMessage('phone:' + target.href);
        return false;
      }
      return true;
    });
    
    // Also find phone numbers in text and make them clickable
    function phoneLinks() {
      const elements = document.querySelectorAll('p, div, span, li, td');
      const phoneRegex = /(+?d[ds-]{7,}d)/g;
      
      elements.forEach(el => {
        if (el.childNodes.length === 1 && el.childNodes[0].nodeType === Node.TEXT_NODE) {
          const text = el.innerText;
          if (phoneRegex.test(text)) {
            el.innerHTML = text.replace(phoneRegex, '<a href="tel:1">1</a>');
          }
        }
      });
    }
    
    // Run initially and whenever content changes
    phoneLinks();
    setInterval(phoneLinks, 1000);
  })();
''');
    } catch (e) {
      debugPrint('Error enabling full functionality: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async {
        if (_controller != null && await _controller!.canGoBack()) {
          _controller!.goBack();
          return false;
        }
        return true;
      },
      child: Scaffold(
        backgroundColor: Colors.white,
        body: SafeArea(
          child: Stack(
            children: [
              if (_isOffline || _hasError)
                NoInternetConnection(onRetry: _refreshPage)
              else if (_controller == null)
                const Center(
                  child: CircularProgressIndicator(),
                )
              else
                // Proper pull-to-refresh implementation
                RefreshIndicator(
                  onRefresh: _refreshPage,
                  child: Stack(
                    children: [
                      WebViewWidget(controller: _controller!),
                      // This empty container ensures the refresh indicator works properly
                      Container(
                        color: Colors.transparent,
                        height: 0.1,
                      ),
                    ],
                  ),
                ),
              if (_isFileUploading)
                const Center(child: CircularProgressIndicator()),
              if (_isLoading && _controller != null)
                LinearProgressIndicator(
                  value: _progress,
                  backgroundColor: Colors.grey[300],
                  valueColor: AlwaysStoppedAnimation<Color>(
                    Theme.of(context).primaryColor,
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
