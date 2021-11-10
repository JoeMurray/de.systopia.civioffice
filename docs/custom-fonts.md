# Using custom fonts
If you want to save certain fonts in your docx document, please note the following procedure in Libre Office (MS-Office might be likewise):
```
File -> Properties -> Font -> Font embedding : set tick at "Embed fonts in the document"
(You have to repeat this for any document, as this is not enabled globally!)
Save as -> .docx
```
The file-size will increase drastically, as you are saving all fonts in the document.
Further note that this may not include all weights and styles of the fonts (Light, Bold, Italic etc.). While working with .pdfs can be rather reliable, working with docx and different software (LibreOffice, Excel, GoogleDocs, etc.) could prove complicated.

If you want to work with certain individual fonts on a regular basis, it is advisable to store them on the server itself, making it available to all documents. The common formats for fonts are OTF (OpenType) and TTF (TrueType).
The common places for these are:
```
/usr/share/fonts/opentype
/usr/share/fonts/truetype
```
As a rule, you have to ask your hosting-provider to install the fonts, but generally you just have to create the respective directory, copy the font-file there and recreate the fonts cache with something like:
``sudo fc-cache -f -v``